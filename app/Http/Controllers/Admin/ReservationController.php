<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Reservation\CreateReservationAction;
use App\Actions\Admin\Reservation\DeleteReservationAction;
use App\Actions\Admin\Reservation\GetReservationsListAction;
use App\Actions\Admin\Reservation\UpdateReservationAction;
use App\DTOs\Admin\Reservation\AdminReservationListData;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Exceptions\InsufficientSeatsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reservation\CreateReservationRequest;
use App\Http\Requests\Admin\Reservation\IndexReservationsRequest;
use App\Http\Requests\Admin\Reservation\UpdateReservationRequest;
use App\Models\Reservation;
use App\Queries\Builders\TripQueryBuilder;
use App\Queries\Builders\UserQueryBuilder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for admin reservation management operations.
 */
final class ReservationController extends Controller
{
    /**
     * Display a listing of reservations with filtering and pagination.
     */
    public function index(
        IndexReservationsRequest $request,
        GetReservationsListAction $action
    ): Response {
        $listData = AdminReservationListData::fromRequest($request->validated());
        $reservations = $action->execute($listData);

        return Inertia::render('admin/reservations/index', [
            'reservations' => $reservations,
            'filters' => $request->only([
                'search',
                'status',
                'user_id',
                'trip_id',
                'start_date',
                'end_date',
                'upcoming_only',
            ]),
            'statusOptions' => ReservationStatus::options(),
        ]);
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create(): Response
    {
        $users = (new UserQueryBuilder(['id', 'name', 'email']))
            ->orderByName()
            ->withRole(Role::USER->value)
            ->active()
            ->get();

        $trips = (new TripQueryBuilder(['id', 'origin_city_id', 'destination_city_id', 'departure_time', 'price', 'bus_id', 'is_active']))
            ->with([
                'bus:id,bus_code,capacity',
                'originCity:id,name,code',
                'destinationCity:id,name,code',
                'reservations' => function ($query): void {
                    $query->where('status', '!=', ReservationStatus::CANCELLED)
                        ->select('trip_id', 'seats_count');
                },
            ])
            ->active()
            ->orderByDeparture()
            ->get();

        return Inertia::render('admin/reservations/create', [
            'users' => $users,
            'trips' => $trips,
            'statusOptions' => ReservationStatus::options(),
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
            $reservation = $action->execute($reservationData);

            return redirect()
                ->route('admin.reservations.index')
                ->with('success', sprintf('Reservation %s created successfully.', $reservation->reservation_code));
        } catch (InsufficientSeatsException $insufficientSeatsException) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $insufficientSeatsException->getMessage());
        }
    }

    /**
     * Show the form for editing a reservation.
     */
    public function edit(Reservation $reservation): Response
    {
        $reservation->load(['user', 'trip.bus']);

        $users = (new UserQueryBuilder(['id', 'name', 'email']))
            ->orderByName()
            ->get();

        $trips = (new TripQueryBuilder(['id', 'origin_city_id', 'destination_city_id', 'departure_time', 'price', 'bus_id', 'is_active']))
            ->with([
                'bus:id,bus_code,capacity',
                'originCity:id,name,code',
                'destinationCity:id,name,code',
                'reservations' => function ($query): void {
                    $query->where('status', '!=', \App\Enums\ReservationStatus::CANCELLED)
                        ->select('trip_id', 'seats_count');
                },
            ])
            ->active()
            ->orderByDeparture()
            ->get()
            ->map(function ($trip) use ($reservation): \App\Models\Trip {
                $trip->available_seats = $trip->getAvailableSeatsExcluding($reservation->id);

                return $trip;
            });

        return Inertia::render('admin/reservations/edit', [
            'reservation' => $reservation,
            'users' => $users,
            'trips' => $trips,
            'statusOptions' => ReservationStatus::options(),
        ]);
    }

    /**
     * Update the specified reservation.
     */
    public function update(
        UpdateReservationRequest $request,
        Reservation $reservation,
        UpdateReservationAction $action
    ): RedirectResponse {
        try {
            $reservationData = $request->toDTO();
            $updatedReservation = $action->execute($reservation, $reservationData);

            return redirect()
                ->route('admin.reservations.index')
                ->with('success', sprintf('Reservation %s updated successfully.', $updatedReservation->reservation_code));
        } catch (InsufficientSeatsException $insufficientSeatsException) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $insufficientSeatsException->getMessage());
        }
    }

    /**
     * Remove the specified reservation.
     */
    public function destroy(
        Reservation $reservation,
        DeleteReservationAction $action
    ): RedirectResponse {
        $reservationCode = $reservation->reservation_code;
        $action->execute($reservation);

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', sprintf('Reservation %s deleted successfully.', $reservationCode));
    }
}
