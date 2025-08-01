<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bus;

use App\DTOs\Admin\Bus\BusData;
use App\Models\Bus;

/**
 * Action for creating new buses.
 */
final class CreateBusAction
{
    /**
     * Create a new bus.
     */
    public function execute(BusData $data): Bus
    {
        return Bus::create($data->toArray());
    }
}
