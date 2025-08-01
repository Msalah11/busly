<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\TripData;
use App\Models\Trip;

/**
 * Action for updating trips.
 */
final class UpdateTripAction
{
    /**
     * Update a trip.
     */
    public function execute(Trip $trip, TripData $data): Trip
    {
        $trip->update($data->toArray());

        return $trip->fresh();
    }
}
