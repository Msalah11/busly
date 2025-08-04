<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReservationSeat Model
 *
 * Represents an individual seat within a reservation.
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $seat_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReservationSeat extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationSeatFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reservation_id',
        'seat_number',
    ];

    /**
     * Get the reservation that this seat belongs to.
     *
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
