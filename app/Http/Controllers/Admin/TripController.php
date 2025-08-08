<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Trip\CreateTripAction;
use App\Actions\Admin\Trip\DeleteTripAction;
use App\Actions\Admin\Trip\GetTripsListAction;
use App\Actions\Admin\Trip\UpdateTripAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trip\CreateTripRequest;
use App\Http\Requests\Admin\Trip\IndexTripsRequest;
use App\Http\Requests\Admin\Trip\UpdateTripRequest;
use App\Models\Trip;
use App\Queries\Builders\BusQueryBuilder;
use App\Queries\Builders\CityQueryBuilder;
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
    public function index(IndexTripsRequest $request, GetTripsListAction $action): Response
    {
        $listData = $request->toDTO();
        $trips = $action->execute($listData);
        
        $buses = (new BusQueryBuilder(['id', 'bus_code', 'capacity', 'type']))
            ->active()
            ->get();
            
        $cities = (new CityQueryBuilder())->getSelectOptions();

        return Inertia::render('admin/trips/index', [
            'trips' => $trips,
            'buses' => $buses,
            'cities' => $cities,
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
            ->get();

        $cities = (new CityQueryBuilder())->getSelectOptions();

        return Inertia::render('admin/trips/create', [
            'buses' => $buses,
            'cities' => $cities,
        ]);
    }

    /**
     * Store a newly created trip in storage.
     */
    public function store(CreateTripRequest $request, CreateTripAction $action): RedirectResponse
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
            ->get();

        $cities = (new CityQueryBuilder())->getSelectOptions();

        return Inertia::render('admin/trips/edit', [
            'trip' => $trip->load(['bus', 'originCity', 'destinationCity']),
            'buses' => $buses,
            'cities' => $cities,
        ]);
    }

    /**
     * Update the specified trip in storage.
     */
    public function update(UpdateTripRequest $request, Trip $trip, UpdateTripAction $action): RedirectResponse
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
