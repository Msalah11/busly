<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\User\Reservation\CancelReservationAction;
use App\Actions\User\Reservation\CreateReservationAction;
use App\Actions\User\Reservation\GetUserReservationsAction;
use App\DTOs\User\Reservation\UserReservationListData;
use App\Enums\ReservationStatus;
use App\Exceptions\InsufficientSeatsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Reservation\CreateReservationRequest;
use App\Http\Requests\User\Reservation\IndexReservationsRequest;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for user reservation management operations.
 */
final class ReservationController extends Controller
{
    /**
     * Display a listing of user's reservations with filtering and pagination.
     */
    public function index(
        IndexReservationsRequest $request,
        GetUserReservationsAction $action
    ): Response {
        $listData = UserReservationListData::fromRequest($request->validated());
        $reservations = $action->execute($listData, Auth::id());

        return Inertia::render('user/reservations/index', [
            'reservations' => $reservations,
            'filters' => $request->only([
                'search',
                'status',
                'start_date',
                'end_date',
                'upcoming_only',
            ]),
            'statusOptions' => ReservationStatus::options(),
        ]);
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation): Response
    {
        // Ensure the reservation belongs to the authenticated user
        if ($reservation->user_id !== Auth::id()) {
            abort(404);
        }

        $reservation->load([
            'trip.bus',
            'trip.originCity',
            'trip.destinationCity',
        ]);

        return Inertia::render('user/reservations/show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(
        CreateReservationRequest $request,
        CreateReservationAction $action
    ): RedirectResponse {
        try {
            $reservationData = $request->toDTO();
            $reservation = $action->execute($reservationData, Auth::id());

            return redirect()
                ->route('user.reservations.show', $reservation)
                ->with('success', sprintf('Reservation %s created successfully!', $reservation->reservation_code));
        } catch (InsufficientSeatsException $insufficientSeatsException) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $insufficientSeatsException->getMessage());
        } catch (\InvalidArgumentException $invalidArgumentException) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $invalidArgumentException->getMessage());
        }
    }

    /**
     * Cancel the specified reservation.
     */
    public function destroy(
        Reservation $reservation,
        CancelReservationAction $action
    ): RedirectResponse {
        try {
            $reservationCode = $reservation->reservation_code;
            $action->execute($reservation, Auth::id());

            return redirect()
                ->route('user.reservations.index')
                ->with('success', sprintf('Reservation %s has been cancelled.', $reservationCode));
        } catch (\InvalidArgumentException $invalidArgumentException) {
            return redirect()
                ->back()
                ->with('error', $invalidArgumentException->getMessage());
        }
    }
}
