<?php

declare(strict_types=1);

use App\Actions\Admin\Reservation\GetReservationsListAction;
use App\DTOs\Admin\Reservation\AdminReservationListData;
use App\Enums\ReservationStatus;
use App\Models\Bus;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->action = new GetReservationsListAction;
});

describe('GetReservationsListAction', function (): void {
    it('can get reservations list with default parameters', function (): void {
        Reservation::factory()->count(5)->create();

        $data = new AdminReservationListData;
        $result = $this->action->execute($data);

        expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
        expect($result->count())->toBe(5);
        expect($result->perPage())->toBe(15);
    });

    it('can search reservations by reservation code', function (): void {
        Reservation::factory()->create(['reservation_code' => 'RES-ABC123']);
        Reservation::factory()->create(['reservation_code' => 'RES-XYZ789']);
        Reservation::factory()->create(['reservation_code' => 'RES-DEF456']);

        $data = new AdminReservationListData(search: 'ABC123');
        $result = $this->action->execute($data);

        expect($result->count())->toBe(1);
        expect($result->first()->reservation_code)->toBe('RES-ABC123');
    });

    it('can filter reservations by status', function (): void {
        Reservation::factory()->confirmed()->count(3)->create();
        Reservation::factory()->cancelled()->count(2)->create();

        $confirmedData = new AdminReservationListData(status: ReservationStatus::CONFIRMED);
        $confirmedResult = $this->action->execute($confirmedData);

        $cancelledData = new AdminReservationListData(status: ReservationStatus::CANCELLED);
        $cancelledResult = $this->action->execute($cancelledData);

        expect($confirmedResult->count())->toBe(3);
        expect($cancelledResult->count())->toBe(2);
    });

    it('can filter reservations by user', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Reservation::factory()->for($user1)->count(3)->create();
        Reservation::factory()->for($user2)->count(2)->create();

        $data = new AdminReservationListData(userId: $user1->id);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(3);
        expect($result->every(fn ($reservation) => $reservation->user_id === $user1->id))->toBeTrue();
    });

    it('can filter reservations by trip', function (): void {
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        Reservation::factory()->for($trip1)->count(4)->create();
        Reservation::factory()->for($trip2)->count(1)->create();

        $data = new AdminReservationListData(tripId: $trip1->id);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(4);
        expect($result->every(fn ($reservation) => $reservation->trip_id === $trip1->id))->toBeTrue();
    });

    it('can filter reservations by date range', function (): void {
        $startDate = \Carbon\Carbon::now()->subWeek();
        $endDate = \Carbon\Carbon::now()->subDay();

        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subWeeks(2)]); // Outside range
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subDays(5)]); // Inside range
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subDays(3)]); // Inside range
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()]); // Outside range

        $data = new AdminReservationListData(
            startDate: $startDate,
            endDate: $endDate
        );
        $result = $this->action->execute($data);

        expect($result->count())->toBe(2);
    });

    it('includes user and trip relationship data', function (): void {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $bus = Bus::factory()->create(['bus_code' => 'BUS123']);
        $trip = Trip::factory()->forBus($bus)->routeByName('Cairo', 'Alexandria')->create();
        
        Reservation::factory()->for($user)->for($trip)->create();

        $data = new AdminReservationListData;
        $result = $this->action->execute($data);

        $reservation = $result->first();
        expect($reservation->user)->not->toBeNull();
        expect($reservation->trip)->not->toBeNull();
        expect($reservation->user->name)->toBe('John Doe');
        expect($reservation->trip->route)->toContain('Cairo');
        expect($reservation->trip->bus)->not->toBeNull();
        expect($reservation->trip->bus->bus_code)->toBe('BUS123');
    });

    it('orders reservations by creation date descending', function (): void {
        $oldReservation = Reservation::factory()->create(['created_at' => \Carbon\Carbon::now()->subDays(2)]);
        $newReservation = Reservation::factory()->create(['created_at' => \Carbon\Carbon::now()]);

        $data = new AdminReservationListData;
        $result = $this->action->execute($data);

        expect($result->first()->id)->toBe($newReservation->id);
        expect($result->last()->id)->toBe($oldReservation->id);
    });

    it('can combine multiple filters', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        // Create reservations with different combinations
        Reservation::factory()->for($user1)->for($trip1)->confirmed()->create(['reservation_code' => 'RES-MATCH1']);
        Reservation::factory()->for($user1)->for($trip1)->cancelled()->create(['reservation_code' => 'RES-NOMATCH1']);
        Reservation::factory()->for($user2)->for($trip1)->confirmed()->create(['reservation_code' => 'RES-NOMATCH2']);
        Reservation::factory()->for($user1)->for($trip2)->confirmed()->create(['reservation_code' => 'RES-NOMATCH3']);

        $data = new AdminReservationListData(
            search: 'MATCH',
            status: ReservationStatus::CONFIRMED,
            userId: $user1->id,
            tripId: $trip1->id
        );
        $result = $this->action->execute($data);

        expect($result->count())->toBe(1);
        expect($result->first()->reservation_code)->toBe('RES-MATCH1');
    });

    it('returns empty result when no matches found', function (): void {
        Reservation::factory()->count(3)->create();

        $data = new AdminReservationListData(search: 'NONEXISTENT');
        $result = $this->action->execute($data);

        expect($result->count())->toBe(0);
        expect($result->total())->toBe(0);
    });

    it('handles pagination correctly', function (): void {
        Reservation::factory()->count(25)->create();

        $data = new AdminReservationListData;
        $result = $this->action->execute($data);

        expect($result->perPage())->toBe(15);
        expect($result->count())->toBe(15); // First page
        expect($result->total())->toBe(25);
        expect($result->hasMorePages())->toBeTrue();
    });

    it('can filter by date range with only start date', function (): void {
        $startDate = \Carbon\Carbon::now()->subDays(3);

        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subWeek()]); // Before start date
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subDay()]); // After start date
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()]); // After start date

        $data = new AdminReservationListData(startDate: $startDate);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(2);
    });

    it('can filter by date range with only end date', function (): void {
        $endDate = \Carbon\Carbon::now()->subDay();

        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subWeek()]); // Before end date
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()->subDays(2)]); // Before end date
        Reservation::factory()->create(['reserved_at' => \Carbon\Carbon::now()]); // After end date

        $data = new AdminReservationListData(endDate: $endDate);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(2);
    });

    it('handles null search gracefully', function (): void {
        Reservation::factory()->count(3)->create();

        $data = new AdminReservationListData(search: null);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(3);
    });

    it('handles null status filter gracefully', function (): void {
        Reservation::factory()->confirmed()->count(2)->create();
        Reservation::factory()->cancelled()->count(1)->create();

        $data = new AdminReservationListData(status: null);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(3); // Should return all statuses
    });

    it('handles null user filter gracefully', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Reservation::factory()->for($user1)->count(2)->create();
        Reservation::factory()->for($user2)->count(1)->create();

        $data = new AdminReservationListData(userId: null);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(3); // Should return all users
    });

    it('handles null trip filter gracefully', function (): void {
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        Reservation::factory()->for($trip1)->count(2)->create();
        Reservation::factory()->for($trip2)->count(1)->create();

        $data = new AdminReservationListData(tripId: null);
        $result = $this->action->execute($data);

        expect($result->count())->toBe(3); // Should return all trips
    });

    it('can search with case insensitive matching', function (): void {
        Reservation::factory()->create(['reservation_code' => 'RES-ABC123']);
        Reservation::factory()->create(['reservation_code' => 'RES-xyz789']);

        $data = new AdminReservationListData(search: 'abc');
        $result = $this->action->execute($data);

        expect($result->count())->toBe(1);
        expect($result->first()->reservation_code)->toBe('RES-ABC123');
    });


});