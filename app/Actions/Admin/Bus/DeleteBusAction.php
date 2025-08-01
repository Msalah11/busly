<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bus;

use App\Models\Bus;
use App\Queries\Builders\BusQueryBuilder;
use InvalidArgumentException;

/**
 * Action for deleting buses.
 */
final class DeleteBusAction
{
    /**
     * Delete a bus.
     *
     * @throws InvalidArgumentException
     */
    public function execute(Bus $bus): void
    {
        if (BusQueryBuilder::hasActiveTrips($bus)) {
            throw new InvalidArgumentException('Cannot delete bus with active trips.');
        }

        $bus->delete();
    }
}
