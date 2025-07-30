<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Reservation Model
 *
 * Represents a user's reservation for a trip.
 *
 * @property int $id
 * @property string $reservation_code
 * @property int $user_id
 * @property int $trip_id
 * @property int $seats_count
 * @property float $total_price
 * @property ReservationStatus $status
 * @property \Carbon\Carbon $reserved_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Reservation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reservation_code',
        'user_id',
        'trip_id',
        'seats_count',
        'total_price',
        'status',
        'reserved_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ReservationStatus::class,
        'total_price' => 'decimal:2',
        'reserved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation): void {
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = 'RES-'.strtoupper(Str::random(8));
            }

            if (empty($reservation->reserved_at)) {
                $reservation->reserved_at = now();
            }
        });
    }

    /**
     * Get the user that owns the reservation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trip that this reservation belongs to.
     *
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the seats for this reservation.
     *
     * @return HasMany<ReservationSeat, $this>
     */
    public function seats(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }
}
