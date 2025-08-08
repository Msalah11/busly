<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when there are insufficient seats for a reservation.
 */
final class InsufficientSeatsException extends InvalidArgumentException
{
    public function __construct(int $requested, int $available)
    {
        parent::__construct(
            sprintf('Requested %d seats but only %d available', $requested, $available)
        );
    }
}
