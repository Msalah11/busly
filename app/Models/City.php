<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * City Model
 *
 * Represents cities available for bus travel.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'latitude',
        'longitude',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get trips that originate from this city.
     *
     * @return HasMany<Trip, $this>
     */
    public function originTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'origin_city_id');
    }

    /**
     * Get trips that have this city as destination.
     *
     * @return HasMany<Trip, $this>
     */
    public function destinationTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'destination_city_id');
    }
}
