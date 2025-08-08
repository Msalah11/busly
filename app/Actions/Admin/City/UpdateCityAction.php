<?php

declare(strict_types=1);

namespace App\Actions\Admin\City;

use App\DTOs\Admin\City\CityData;
use App\Models\City;

/**
 * Action to update an existing city.
 */
final class UpdateCityAction
{
    /**
     * Execute the action to update an existing city.
     */
    public function execute(City $city, CityData $data): City
    {
        $city->update($data->toArray());

        return $city->fresh();
    }
}
