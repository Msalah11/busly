<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateBusAction;
use App\Actions\Admin\DeleteBusAction;
use App\Actions\Admin\GetBusesListAction;
use App\Actions\Admin\UpdateBusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBusRequest;
use App\Http\Requests\Admin\IndexBusRequest;
use App\Http\Requests\Admin\UpdateBusRequest;
use App\Models\Bus;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/**
 * Controller for admin bus management operations.
 */
class BusController extends Controller
{
    /**
     * Display a listing of the buses.
     */
    public function index(IndexBusRequest $request, GetBusesListAction $action): Response
    {
        $listData = $request->toDTO();
        $buses = $action->execute($listData);

        return Inertia::render('admin/buses/index', [
            'buses' => $buses,
            'filters' => [
                'search' => $listData->search,
                'type' => $listData->type,
                'active' => $listData->active,
            ],
        ]);
    }

    /**
     * Show the form for creating a new bus.
     */
    public function create(): Response
    {
        return Inertia::render('admin/buses/create');
    }

    /**
     * Store a newly created bus in storage.
     */
    public function store(CreateBusRequest $request, CreateBusAction $action): RedirectResponse
    {
        $busData = $request->toDTO();
        $action->execute($busData);

        return redirect()->route('admin.buses.index')
            ->with('success', 'Bus created successfully.');
    }

    /**
     * Show the form for editing the specified bus.
     */
    public function edit(Bus $bus): Response
    {
        return Inertia::render('admin/buses/edit', [
            'bus' => $bus,
        ]);
    }

    /**
     * Update the specified bus in storage.
     */
    public function update(UpdateBusRequest $request, Bus $bus, UpdateBusAction $action): RedirectResponse
    {
        $busData = $request->toDTO();
        $action->execute($bus, $busData);

        return redirect()->route('admin.buses.index')
            ->with('success', 'Bus updated successfully.');
    }

    /**
     * Remove the specified bus from storage.
     */
    public function destroy(Bus $bus, DeleteBusAction $action): RedirectResponse
    {
        try {
            $action->execute($bus);

            return redirect()->route('admin.buses.index')
                ->with('success', 'Bus deleted successfully.');
        } catch (InvalidArgumentException $invalidArgumentException) {
            return redirect()->route('admin.buses.index')
                ->with('error', $invalidArgumentException->getMessage());
        }
    }
}
