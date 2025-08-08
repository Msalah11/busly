<?php

declare(strict_types=1);

namespace App\Actions\Admin\City;

use App\Exceptions\CityHasTripsException;
use App\Models\City;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Action to delete a city.
 */
final class DeleteCityAction
{
    /**
     * Execute the action to delete a city.
     *
     * @throws ModelNotFoundException
     * @throws CityHasTripsException When city has associated trips
     */
    public function execute(City $city): bool
    {
        // Check if city has any trips
        $tripsCount = (new TripQueryBuilder)
            ->build()
            ->where('origin_city_id', $city->id)
            ->orWhere('destination_city_id', $city->id)
            ->count();

        if ($tripsCount > 0) {
            throw new CityHasTripsException($city->name, $tripsCount);
        }

        return $city->delete();
    }
}
