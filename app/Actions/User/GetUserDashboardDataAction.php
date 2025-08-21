<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Queries\Builders\ReservationQueryBuilder;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Support\Facades\Auth;

/**
 * Action to get user dashboard data.
 */
final class GetUserDashboardDataAction
{
    /**
     * Execute the action to get user dashboard data.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $userId = Auth::id();

        // Get user's recent reservations
        $recentReservations = (new ReservationQueryBuilder)
            ->with([
                'trip:id,origin_city_id,destination_city_id,departure_time,price,bus_id',
                'trip.originCity:id,name',
                'trip.destinationCity:id,name',
                'trip.bus:id,bus_code,type',
            ])
            ->forUser($userId)
            ->orderByCreated()
            ->build()
            ->limit(5)
            ->get();

        // Get user's upcoming reservations
        $upcomingReservations = (new ReservationQueryBuilder)
            ->with([
                'trip:id,origin_city_id,destination_city_id,departure_time,price,bus_id',
                'trip.originCity:id,name',
                'trip.destinationCity:id,name',
                'trip.bus:id,bus_code,type',
            ])
            ->forUser($userId)
            ->upcoming()
            ->confirmed()
            ->orderByDeparture()
            ->build()
            ->limit(5)
            ->get();

        // Get available trips for quick booking
        $availableTrips = (new TripQueryBuilder)
            ->with([
                'bus:id,bus_code,capacity,type',
                'originCity:id,name',
                'destinationCity:id,name',
            ])
            ->active()
            ->upcoming()
            ->withAvailableSeats(1)
            ->orderByDeparture()
            ->build()
            ->limit(6)
            ->get()
            ->map(function ($trip): \App\Models\Trip {
                $trip->available_seats = $trip->getAvailableSeatsAttribute();

                return $trip;
            });

        // Get user reservation statistics using query builder
        $userReservationStats = (new ReservationQueryBuilder)->forUser($userId)->getStatistics();

        // Add user-specific additional statistics
        $userReservationStats['upcoming'] = (new ReservationQueryBuilder)->forUser($userId)->upcoming()->confirmed()->get()->count();
        $userReservationStats['completed'] = (new ReservationQueryBuilder)->forUser($userId)->confirmed()
            ->build()
            ->join('trips', 'reservations.trip_id', '=', 'trips.id')
            ->where('trips.departure_time', '<', now())
            ->count();

        $stats = $userReservationStats;

        return [
            'recentReservations' => $recentReservations,
            'upcomingReservations' => $upcomingReservations,
            'availableTrips' => $availableTrips,
            'stats' => $stats,
        ];
    }
}
