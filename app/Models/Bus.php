<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bus Model
 *
 * Represents a bus in the fleet with its capacity and type.
 *
 * @property int $id
 * @property string $bus_code
 * @property int $capacity
 * @property BusType $type
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Bus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'bus_code',
        'capacity',
        'type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => BusType::class,
        'is_active' => 'boolean',
    ];

    /**
     * Get all trips for this bus.
     *
     * @return HasMany<Trip, $this>
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
