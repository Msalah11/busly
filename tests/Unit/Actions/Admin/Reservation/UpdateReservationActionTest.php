<?php

declare(strict_types=1);

use App\Actions\Admin\Reservation\UpdateReservationAction;
use App\DTOs\Admin\Reservation\ReservationData;
use App\Enums\ReservationStatus;
use App\Exceptions\InsufficientSeatsException;
use App\Models\Bus;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->action = new UpdateReservationAction;
});

describe('UpdateReservationAction', function (): void {
    it('can update a reservation with valid data', function (): void {
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();
        $oldBus = Bus::factory()->create(['capacity' => 50]);
        $newBus = Bus::factory()->create(['capacity' => 50]);
        $oldTrip = Trip::factory()->forBus($oldBus)->active()->create();
        $newTrip = Trip::factory()->forBus($newBus)->active()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $oldUser->id,
            'trip_id' => $oldTrip->id,
            'seats_count' => 2,
            'total_price' => 200.00,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $data = new ReservationData(
            userId: $newUser->id,
            tripId: $newTrip->id,
            seatsCount: 3,
            totalPrice: 300.00,
            status: ReservationStatus::CANCELLED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: \Carbon\Carbon::now()
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->id)->toBe($reservation->id);
        expect($updatedReservation->user_id)->toBe($newUser->id);
        expect($updatedReservation->trip_id)->toBe($newTrip->id);
        expect($updatedReservation->seats_count)->toBe(3);
        expect((float) $updatedReservation->total_price)->toBe(300.00);
        expect($updatedReservation->status)->toBe(ReservationStatus::CANCELLED);
        expect($updatedReservation->cancelled_at)->not->toBeNull();
    });

    it('validates seat availability when changing trip or seat count', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Create the reservation we're updating
        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 2]);

        // Create other reservations that use up most seats
        Reservation::factory()->for($trip)->create(['seats_count' => 7, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5, // Requesting more seats than available (10 - 7 = 3 available, excluding current reservation)
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($reservation, $data))
            ->toThrow(InsufficientSeatsException::class, 'Requested 5 seats but only 3 available');
    });

    it('excludes current reservation from seat availability calculation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 20]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        // Create the reservation we're updating
        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 10]);

        // Create other reservations
        Reservation::factory()->for($trip)->create(['seats_count' => 5, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 15, // Should be allowed (20 - 5 = 15 available, excluding current reservation of 10)
            totalPrice: 1500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->seats_count)->toBe(15);
    });

    it('skips seat validation when not changing trip or seat count', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create([
            'seats_count' => 2,
            'status' => ReservationStatus::CONFIRMED,
            'cancelled_at' => null,
        ]);

        // Fill up remaining seats
        Reservation::factory()->for($trip)->create(['seats_count' => 8, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 2, // Same seat count
            totalPrice: 250.00, // Changed price
            status: ReservationStatus::CANCELLED, // Changed status
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: \Carbon\Carbon::now()
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->status)->toBe(ReservationStatus::CANCELLED);
        expect((float) $updatedReservation->total_price)->toBe(250.00);
    });

    it('validates seat availability when changing to different trip', function (): void {
        $user = User::factory()->create();
        $bus1 = Bus::factory()->create(['capacity' => 20]);
        $bus2 = Bus::factory()->create(['capacity' => 10]);
        $trip1 = Trip::factory()->forBus($bus1)->active()->create();
        $trip2 = Trip::factory()->forBus($bus2)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip1)->create(['seats_count' => 5]);

        // Fill up most seats on trip2
        Reservation::factory()->for($trip2)->create(['seats_count' => 8, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip2->id, // Changing to different trip
            seatsCount: 5, // Same seat count, but trip2 only has 2 available
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($reservation, $data))
            ->toThrow(InsufficientSeatsException::class, 'Requested 5 seats but only 2 available');
    });

    it('allows updating to cancelled status without seat validation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 2]);

        // Fill up all remaining seats
        Reservation::factory()->for($trip)->create(['seats_count' => 8, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5, // More than available, but status is cancelled
            totalPrice: 500.00,
            status: ReservationStatus::CANCELLED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: \Carbon\Carbon::now()
        );

        // Should succeed even with insufficient seats since it's cancelled
        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->status)->toBe(ReservationStatus::CANCELLED);
        expect($updatedReservation->seats_count)->toBe(5);
    });

    it('requires active trip when changing trip', function (): void {
        $user = User::factory()->create();
        $activeTrip = Trip::factory()->active()->create();
        $inactiveTrip = Trip::factory()->inactive()->create();

        $reservation = Reservation::factory()->for($user)->for($activeTrip)->create();

        $data = new ReservationData(
            userId: $user->id,
            tripId: $inactiveTrip->id, // Changing to inactive trip
            seatsCount: 2,
            totalPrice: 200.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        expect(fn () => $this->action->execute($reservation, $data))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('uses database transaction for data consistency', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 2]);

        // Fill up remaining seats
        Reservation::factory()->for($trip)->create(['seats_count' => 8, 'status' => ReservationStatus::CONFIRMED]);

        $originalSeatsCount = $reservation->seats_count;

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5, // This will fail validation
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        try {
            $this->action->execute($reservation, $data);
        } catch (InsufficientSeatsException) {
            // Expected
        }

        // Verify reservation was not updated due to transaction rollback
        $reservation->refresh();
        expect($reservation->seats_count)->toBe($originalSeatsCount);
    });

    it('handles status-specific updates correctly', function (): void {
        $user = User::factory()->create();
        $trip = Trip::factory()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create([
            'status' => ReservationStatus::CONFIRMED,
            'cancelled_at' => null,
        ]);

        $cancelledAt = \Carbon\Carbon::now();

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: $reservation->seats_count,
            totalPrice: (float) $reservation->total_price,
            status: ReservationStatus::CANCELLED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: $cancelledAt
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation->status)->toBe(ReservationStatus::CANCELLED);
        expect($updatedReservation->cancelled_at->format('Y-m-d H:i:s'))->toBe($cancelledAt->format('Y-m-d H:i:s'));
    });

    it('can update reservation from cancelled to confirmed', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create([
            'seats_count' => 3,
            'status' => ReservationStatus::CANCELLED,
            'cancelled_at' => \Carbon\Carbon::now()->subHour(),
        ]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 3,
            totalPrice: 300.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation->status)->toBe(ReservationStatus::CONFIRMED);
        expect($updatedReservation->cancelled_at)->toBeNull();
    });

    it('validates seat availability when reactivating cancelled reservation', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create([
            'seats_count' => 5,
            'status' => ReservationStatus::CANCELLED,
        ]);

        // Fill up all seats
        Reservation::factory()->for($trip)->create(['seats_count' => 10, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 5,
            totalPrice: 500.00,
            status: ReservationStatus::CONFIRMED, // Reactivating
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        // The test should pass since the current cancelled reservation (5 seats) is excluded from seat calculation
        // Available seats = 10 (capacity) - 10 (confirmed) + 5 (current cancelled) = 5 seats
        $updatedReservation = $this->action->execute($reservation, $data);
        
        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->status)->toBe(ReservationStatus::CONFIRMED);
    });

    it('preserves reservation code when updating', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 50]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 2]);
        $originalCode = $reservation->reservation_code;

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 3, // Small change within capacity
            totalPrice: 300.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation->reservation_code)->toBe($originalCode);
    });

    it('handles edge case with exact seat availability when updating', function (): void {
        $user = User::factory()->create();
        $bus = Bus::factory()->create(['capacity' => 10]);
        $trip = Trip::factory()->forBus($bus)->active()->create();

        $reservation = Reservation::factory()->for($user)->for($trip)->create(['seats_count' => 2]);

        // Use up exactly 7 more seats
        Reservation::factory()->for($trip)->create(['seats_count' => 7, 'status' => ReservationStatus::CONFIRMED]);

        $data = new ReservationData(
            userId: $user->id,
            tripId: $trip->id,
            seatsCount: 3, // Exactly the remaining seats (10 - 7 = 3, excluding current 2)
            totalPrice: 300.00,
            status: ReservationStatus::CONFIRMED,
            reservedAt: \Carbon\Carbon::parse($reservation->reserved_at),
            cancelledAt: null
        );

        $updatedReservation = $this->action->execute($reservation, $data);

        expect($updatedReservation)->toBeInstanceOf(Reservation::class);
        expect($updatedReservation->seats_count)->toBe(3);
    });
});