<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateTripAction;
use App\Actions\Admin\DeleteTripAction;
use App\Actions\Admin\GetTripsListAction;
use App\Actions\Admin\UpdateTripAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminTripCreateRequest;
use App\Http\Requests\Admin\AdminTripsRequest;
use App\Http\Requests\Admin\AdminTripUpdateRequest;
use App\Models\Trip;
use App\Queries\Builders\BusQueryBuilder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for admin trip management operations.
 */
class TripController extends Controller
{
    /**
     * Display a listing of the trips.
     */
    public function index(AdminTripsRequest $request, GetTripsListAction $action): Response
    {
        $listData = $request->toDTO();
        $trips = $action->execute($listData);

        return Inertia::render('admin/trips/index', [
            'trips' => $trips,
            'filters' => [
                'search' => $listData->search,
                'bus_id' => $listData->busId,
                'active' => $listData->active,
            ],
        ]);
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create(): Response
    {
        $buses = (new BusQueryBuilder(['id', 'bus_code', 'capacity', 'type']))
            ->active()
            ->orderBy('bus_code')
            ->get();

        return Inertia::render('admin/trips/create', [
            'buses' => $buses,
        ]);
    }

    /**
     * Store a newly created trip in storage.
     */
    public function store(AdminTripCreateRequest $request, CreateTripAction $action): RedirectResponse
    {
        $tripData = $request->toDTO();
        $action->execute($tripData);

        return redirect()->route('admin.trips.index')
            ->with('success', 'Trip created successfully.');
    }

    /**
     * Show the form for editing the specified trip.
     */
    public function edit(Trip $trip): Response
    {
        $buses = (new BusQueryBuilder(['id', 'bus_code', 'capacity', 'type']))
            ->active()
            ->orderBy('bus_code')
            ->get();

        return Inertia::render('admin/trips/edit', [
            'trip' => $trip->load('bus'),
            'buses' => $buses,
        ]);
    }

    /**
     * Update the specified trip in storage.
     */
    public function update(AdminTripUpdateRequest $request, Trip $trip, UpdateTripAction $action): RedirectResponse
    {
        $tripData = $request->toDTO();
        $action->execute($trip, $tripData);

        return redirect()->route('admin.trips.index')
            ->with('success', 'Trip updated successfully.');
    }

    /**
     * Remove the specified trip from storage.
     */
    public function destroy(Trip $trip, DeleteTripAction $action): RedirectResponse
    {
        // Check if trip has active reservations
        if ($trip->reservations()->exists()) {
            return redirect()->route('admin.trips.index')
                ->with('error', 'Cannot delete trip with existing reservations.');
        }

        $action->execute($trip);

        return redirect()->route('admin.trips.index')
            ->with('success', 'Trip deleted successfully.');
    }
}
