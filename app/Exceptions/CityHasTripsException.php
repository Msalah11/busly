<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when attempting to delete a city that has associated trips.
 */
class CityHasTripsException extends Exception
{
    public function __construct(string $cityName, int $tripsCount)
    {
        $message = sprintf("Cannot delete city '%s' because it has %d associated trip(s). Please remove or reassign the trips first.", $cityName, $tripsCount);

        parent::__construct($message);
    }
}
