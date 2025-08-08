<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\City\CreateCityAction;
use App\Actions\Admin\City\DeleteCityAction;
use App\Actions\Admin\City\GetCitiesListAction;
use App\Actions\Admin\City\UpdateCityAction;
use App\Exceptions\CityHasTripsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\City\CreateCityRequest;
use App\Http\Requests\Admin\City\IndexCitiesRequest;
use App\Http\Requests\Admin\City\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for admin city management operations.
 */
final class CityController extends Controller
{
    /**
     * Display a listing of cities with filtering and pagination.
     */
    public function index(
        IndexCitiesRequest $request,
        GetCitiesListAction $action
    ): Response {
        $listData = $request->toDTO();
        $cities = $action->execute($listData);

        return Inertia::render('admin/cities/index', [
            'cities' => $cities,
            'filters' => $request->only(['search', 'is_active', 'sort_by', 'sort_direction']),
            'sortableColumns' => [
                'name' => 'Name',
                'code' => 'Code',
                'sort_order' => 'Sort Order',
                'created_at' => 'Created Date',
            ],
        ]);
    }

    /**
     * Show the form for creating a new city.
     */
    public function create(): Response
    {
        return Inertia::render('admin/cities/create');
    }

    /**
     * Store a newly created city.
     */
    public function store(
        CreateCityRequest $request,
        CreateCityAction $action
    ): RedirectResponse {
        $cityData = $request->toDTO();
        $city = $action->execute($cityData);

        return redirect()
            ->route('admin.cities.index')
            ->with('success', "City '{$city->name}' created successfully.");
    }

    /**
     * Show the form for editing a city.
     */
    public function edit(City $city): Response
    {
        return Inertia::render('admin/cities/edit', [
            'city' => $city,
        ]);
    }

    /**
     * Update the specified city.
     */
    public function update(
        UpdateCityRequest $request,
        City $city,
        UpdateCityAction $action
    ): RedirectResponse {
        $cityData = $request->toDTO();
        $updatedCity = $action->execute($city, $cityData);

        return redirect()
            ->route('admin.cities.index')
            ->with('success', "City '{$updatedCity->name}' updated successfully.");
    }

    /**
     * Remove the specified city.
     */
    public function destroy(
        City $city,
        DeleteCityAction $action
    ): RedirectResponse {
        try {
            $cityName = $city->name;
            $action->execute($city);

            return redirect()
                ->route('admin.cities.index')
                ->with('success', "City '{$cityName}' deleted successfully.");
        } catch (CityHasTripsException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
