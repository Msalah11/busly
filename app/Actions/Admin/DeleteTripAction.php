<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Trip;

/**
 * Action for deleting trips.
 */
final class DeleteTripAction
{
    /**
     * Delete a trip.
     */
    public function execute(Trip $trip): void
    {
        $trip->delete();
    }
}
