<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Queries\Builders\BusQueryBuilder;
use App\Queries\Builders\TripQueryBuilder;
use App\Queries\Builders\UserQueryBuilder;

/**
 * Action to get comprehensive dashboard data including statistics and recent activity.
 */
class GetDashboardDataAction
{
    /**
     * Execute the action to get dashboard data.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'stats' => $this->getStatistics(),
            'recentBuses' => $this->getRecentBuses(),
            'recentTrips' => $this->getRecentTrips(),
        ];
    }

    /**
     * Get dashboard statistics using query builders.
     *
     * @return array<string, array<string, int>>
     */
    private function getStatistics(): array
    {
        return [
            'users' => (new UserQueryBuilder)->getStatistics(),
            'buses' => (new BusQueryBuilder)->getStatistics(),
            'trips' => (new TripQueryBuilder)->getStatistics(),
        ];
    }

    /**
     * Get recent buses with their active trips using query builder.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Bus>
     */
    private function getRecentBuses(): \Illuminate\Database\Eloquent\Collection
    {
        return (new BusQueryBuilder)->getRecentWithTrips();
    }

    /**
     * Get recent trips with their associated bus data using query builder.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip>
     */
    private function getRecentTrips(): \Illuminate\Database\Eloquent\Collection
    {
        return (new TripQueryBuilder)->getRecentWithBus();
    }
}
