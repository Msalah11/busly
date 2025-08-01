<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bus;

use App\DTOs\Admin\Bus\BusData;
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
