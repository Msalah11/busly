<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\BusData;
use App\Models\Bus;

/**
 * Action for updating buses.
 */
final class UpdateBusAction
{
    /**
     * Update a bus.
     */
    public function execute(Bus $bus, BusData $data): Bus
    {
        $bus->update($data->toArray());

        return $bus->fresh();
    }
} 