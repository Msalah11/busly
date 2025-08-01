<?php

declare(strict_types=1);

namespace App\Actions\Admin\Trip;

use App\DTOs\Admin\Trip\TripData;
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
