<?php

declare(strict_types=1);

use App\Actions\Admin\Reservation\CreateReservationAction;
use App\DTOs\Admin\Reservation\ReservationData;
use App\Enums\ReservationStatus;
use App\Exceptions\InsufficientSeatsException;
use App\Models\Bus;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->action = new CreateReservationAction;
});

describe('CreateReservationAction', function (): void {
    it('can create a reservation with valid data', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 3,
            totalPrice: 300.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        $reservation = $this->action->execute($data);

        expect($reservation)->toBeInstanceOf(Reservation::class);
        expect($reservation->user_id)->toBe($user->id);
        expect($reservation->trip_id)->toBe($trip->id);
        expect($reservation->seats_count)->toBe(3);
        expect((float) $reservation->total_price)->toBe(300.00);
        expect($reservation->status)->toBe(ReservationStatus::CONFIRMED);
        expect($reservation->reservation_code)->toStartWith('RES-');
        expect($reservation->exists)->toBeTrue();
    });

    it('generates unique reservation codes', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        $reservation1 = $this->action->execute($data);
        $reservation2 = $this->action->execute($data);

        expect($reservation1->reservation_code)->not->toBe($reservation2->reservation_code);
        expect($reservation1->reservation_code)->toStartWith('RES-');
        expect($reservation2->reservation_code)->toStartWith('RES-');
    });

    it('validates seat availability before creating reservation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Create existing reservations that use up most seats
        Reservation::factory()->for($trip)->create(['seats_count' => 8, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5, // Requesting more seats than available (10 - 8 = 2 available)
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($data))
            ->toThrow(InsufficientSeatsException::class, 'Requested 5 seats but only 2 available');
    });

    it('excludes cancelled reservations from seat availability calculation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 20]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Create reservations
        Reservation::factory()->for($trip)->create(['seats_count' => 10, 'status' => ReservationStatus::CONFIRMED]);
        Reservation::factory()->for($trip)->create(['seats_count' => 5, 'status' => ReservationStatus::CANCELLED]); // Should not count

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 10, // Should be allowed (20 - 10 = 10 available, cancelled doesn't count)
            totalPrice: 1000.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        $reservation = $this->action->execute($data);

        expect($reservation)->toBeInstanceOf(Reservation::class);
        expect($reservation->seats_count)->toBe(10);
    });

    it('allows creating cancelled reservations without seat validation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Fill up all seats
        Reservation::factory()->for($trip)->create(['seats_count' => 10, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CANCELLED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: \Carbon\Carbon::now()
        );

        // Should succeed even with no available seats since it's cancelled
        $reservation = $this->action->execute($data);

        expect($reservation)->toBeInstanceOf(Reservation::class);
        expect($reservation->status)->toBe(ReservationStatus::CANCELLED);
        expect($reservation->seats_count)->toBe(2);
    });

    it('requires active trip', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->inactive()->create(); // Inactive trip

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($data))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('uses database transaction for data consistency', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Create existing reservations that use up most seats
        Reservation::factory()->for($trip)->create(['seats_count' => 9, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5, // This will fail validation
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        try {
            $this->action->execute($data);
        } catch (InsufficientSeatsException) {
            // Expected
        }

        // Verify no reservation was created due to transaction rollback
        expect(Reservation::where('user_id', $user->id)->where('trip_id', $trip->id)->exists())->toBeFalse();
    });

    it('sets reserved_at timestamp correctly', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->active()->create();
        $reservedAt = \Carbon\Carbon::now()->subHours(2);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: $reservedAt,
            cancelledAt: null
        );

        $reservation = $this->action->execute($data);

        expect($reservation->reserved_at->format('Y-m-d H:i:s'))->toBe($reservedAt->format('Y-m-d H:i:s'));
    });

    it('sets cancelled_at timestamp for cancelled reservations', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->active()->create();
        $cancelledAt = \Carbon\Carbon::now()->subHour();

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CANCELLED,
            reservedAt: \Carbon\Carbon::now()->subHours(2),
            cancelledAt: $cancelledAt
        );

        $reservation = $this->action->execute($data);

        expect($reservation->cancelled_at->format('Y-m-d H:i:s'))->toBe($cancelledAt->format('Y-m-d H:i:s'));
    });

    it('handles edge case with exact seat availability', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Use up exactly 7 seats
        Reservation::factory()->for($trip)->create(['seats_count' => 7, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 3, // Exactly the remaining seats
            totalPrice: 300.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        $reservation = $this->action->execute($data);

        expect($reservation)->toBeInstanceOf(Reservation::class);
        expect($reservation->seats_count)->toBe(3);
    });

    it('prevents overbooking by one seat', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Use up exactly 7 seats
        Reservation::factory()->for($trip)->create(['seats_count' => 7, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 4, // One more than available
            totalPrice: 400.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($data))
            ->toThrow(InsufficientSeatsException::class, 'Requested 4 seats but only 3 available');
    });

    it('handles multiple concurrent reservations correctly', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $data1 = new ReservationData(
            userId: $user1->id,
            tripId: $trip->id,
            seatsCount: 6,
            totalPrice: 600.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        $data2 = new ReservationData(
            userId: $user2->id,
            tripId: $trip->id,
            seatsCount: 4,
            totalPrice: 400.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        // First reservation should succeed
        $reservation1 = $this->action->execute($data1);
        expect($reservation1)->toBeInstanceOf(Reservation::class);

        // Second reservation should succeed (6 + 4 = 10, exactly the capacity)
        $reservation2 = $this->action->execute($data2);
        expect($reservation2)->toBeInstanceOf(Reservation::class);

        // Third reservation should fail
        $data3 = new ReservationData(
            userId: $user1->id,
            tripId: $trip->id,
            seatsCount: 1,
            totalPrice: 100.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::now(),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($data3))
            ->toThrow(InsufficientSeatsException::class);
    });
});
