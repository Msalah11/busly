<?php

declare(strict_types=1);

namespace App\Actions\Admin\City;

use App\DTOs\Admin\City\CityData;
use App\Models\City;

/**
 * Action to create a new city.
 */
final class CreateCityAction
{
    /**
     * Execute the action to create a new city.
     */
    public function execute(CityData $data): City
    {
        return City::create($data->toArray());
    }
}
