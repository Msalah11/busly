<?php

declare(strict_types=1);

namespace App\Actions\Admin\Trip;

use App\DTOs\Admin\Trip\TripData;
use App\Models\Trip;

/**
 * Action for creating new trips.
 */
final class CreateTripAction
{
    /**
     * Create a new trip.
     */
    public function execute(TripData $data): Trip
    {
        return Trip::create($data->toArray());
    }
}
