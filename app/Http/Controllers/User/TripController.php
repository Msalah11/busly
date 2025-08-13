<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\User\Trip\SearchTripsAction;
use App\DTOs\User\Trip\TripSearchData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Trip\SearchTripsRequest;
use App\Models\Trip;
use App\Queries\Builders\CityQueryBuilder;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for user trip browsing operations.
 */
final class TripController extends Controller
{
    /**
     * Display a listing of available trips with search and filtering.
     */
    public function index(
        SearchTripsRequest $request,
        SearchTripsAction $action
    ): Response {
        $searchData = TripSearchData::fromRequest($request->validated());
        $trips = $action->execute($searchData);

        // Get cities for the search form
        $cities = (new CityQueryBuilder(['id', 'name', 'code']))
            ->orderByName()
            ->get();

        return Inertia::render('user/trips/index', [
            'trips' => $trips,
            'cities' => $cities,
            'filters' => $request->only([
                'origin_city_id',
                'destination_city_id',
                'departure_date',
                'min_seats',
                'max_price',
            ]),
        ]);
    }

    /**
     * Display the specified trip with reservation form.
     */
    public function show(Trip $trip): Response
    {
        $trip->load([
            'bus',
            'originCity',
            'destinationCity',
        ]);

        // Calculate available seats
        $trip->available_seats = $trip->getAvailableSeatsAttribute();

        return Inertia::render('user/trips/show', [
            'trip' => $trip,
        ]);
    }
}
