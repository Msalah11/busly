<?php

declare(strict_types=1);

namespace App\Actions\Admin\Reservation;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Action to delete a reservation.
 */
class DeleteReservationAction
{
    /**
     * Execute the action.
     *
     * @throws ModelNotFoundException
     */
    public function execute(Reservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation) {
            // Delete related reservation seats first
            $reservation->seats()->delete();
            
            // Delete the reservation
            return $reservation->delete();
        });
    }
}