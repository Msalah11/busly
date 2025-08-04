<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Trip Model
 *
 * Represents a bus trip from origin to destination.
 *
 * @property int $id
 * @property string $origin
 * @property string $destination
 * @property string $departure_time
 * @property string $arrival_time
 * @property float $price
 * @property int $bus_id
 * @property bool $is_active
 * @property int $available_seats
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'price',
        'bus_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bus that belongs to this trip.
     *
     * @return BelongsTo<Bus, $this>
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get all reservations for this trip.
     *
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get reserved seats for this trip through reservations.
     *
     * @return HasManyThrough<ReservationSeat, Reservation, $this>
     */
    public function reservedSeats(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReservationSeat::class,
            Reservation::class,
            'trip_id',
            'reservation_id',
            'id',
            'id'
        );
    }

    /**
     * Get the number of available seats for this trip.
     */
    public function getAvailableSeatsAttribute(): int
    {
        if (!$this->relationLoaded('bus')) {
            $this->load('bus');
        }

        $totalSeats = $this->bus->capacity;
        $reservedSeats = $this->reservations()
            ->where('status', '!=', ReservationStatus::CANCELLED)
            ->sum('seats_count');

        return max(0, $totalSeats - $reservedSeats);
    }

    /**
     * Get available seats count, excluding a specific reservation.
     */
    public function getAvailableSeatsExcluding(?int $excludeReservationId = null): int
    {
        if (!$this->relationLoaded('bus')) {
            $this->load('bus');
        }

        $totalSeats = $this->bus->capacity;
        $query = $this->reservations()->where('status', '!=', ReservationStatus::CANCELLED);
        
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }
        
        $reservedSeats = $query->sum('seats_count');

        return max(0, $totalSeats - $reservedSeats);
    }
}
