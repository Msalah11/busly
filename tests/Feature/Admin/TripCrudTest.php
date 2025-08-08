<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\User;

describe('Admin Trip CRUD Operations', function (): void {
    beforeEach(function (): void {
        // Create an admin user for each test
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    describe('Trip Index/List', function (): void {
        it('can view the trip index page', function (): void {
            $response = $this->get(route('admin.trips.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/index')
                    ->has('trips')
                    ->has('flash')
                );
        });

        it('displays trips with pagination', function (): void {
            Trip::factory()->count(25)->create();

            $response = $this->get(route('admin.trips.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/index')
                    ->has('trips.data', 15) // Default pagination
                    ->has('trips.links')
                    ->where('trips.total', 25)
                );
        });

        it('can filter trips by search term (city names)', function (): void {
            $trip1 = Trip::factory()->routeByName('Cairo', 'Alexandria')->create();
            $trip2 = Trip::factory()->routeByName('Luxor', 'Aswan')->create();

            $response = $this->get(route('admin.trips.index', ['search' => 'Cairo']));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/index')
                    ->has('trips.data', 1)
                    ->where('trips.data.0.origin_city.name', 'Cairo')
                );
        });

        it('can filter trips by active status', function (): void {
            Trip::factory()->active()->count(3)->create();
            Trip::factory()->inactive()->count(2)->create();

            $response = $this->get(route('admin.trips.index', ['active' => true]));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/index')
                    ->has('trips.data', 3)
                );
        });

        it('includes bus relationship data', function (): void {
            $bus = Bus::factory()->create(['bus_code' => 'BUS123']);
            Trip::factory()->forBus($bus)->create();

            $response = $this->get(route('admin.trips.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/index')
                    ->has('trips.data.0.bus')
                    ->where('trips.data.0.bus.bus_code', 'BUS123')
                );
        });
    });

    describe('Trip Creation', function (): void {
        it('can view the trip creation page', function (): void {
            Bus::factory()->active()->count(3)->create();

            $response = $this->get(route('admin.trips.create'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/create')
                    ->has('buses', 3)
                );
        });

        it('can create a new trip with valid data', function (): void {
            $bus = Bus::factory()->create();
            $originCity = City::factory()->create(['name' => 'Cairo']);
            $destinationCity = City::factory()->create(['name' => 'Alexandria']);

            $tripData = [
                'origin_city_id' => $originCity->id,
                'destination_city_id' => $destinationCity->id,
                'departure_time' => '08:00',
                'arrival_time' => '12:00',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ];

            $response = $this->post(route('admin.trips.store'), $tripData);

            $response->assertRedirect(route('admin.trips.index'))
                ->assertSessionHas('success', 'Trip created successfully.');

            $this->assertDatabaseHas('trips', [
                'origin_city_id' => $originCity->id,
                'destination_city_id' => $destinationCity->id,
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);
        });

        it('validates required fields when creating a trip', function (): void {
            $response = $this->post(route('admin.trips.store'), []);

            $response->assertSessionHasErrors([
                'origin_city_id',
                'destination_city_id',
                'departure_time',
                'arrival_time',
                'price',
                'bus_id',
            ]);
        });

        it('validates origin and destination cities are different', function (): void {
            $bus = Bus::factory()->create();
            $city = City::factory()->create();

            $response = $this->post(route('admin.trips.store'), [
                'origin_city_id' => $city->id,
                'destination_city_id' => $city->id, // Same city - should fail
                'departure_time' => '08:00',
                'arrival_time' => '12:00',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['destination_city_id']);
        });

        it('validates arrival time is after departure time', function (): void {
            $bus = Bus::factory()->create();

            $response = $this->post(route('admin.trips.store'), [
                'origin' => 'Cairo',
                'destination' => 'Alexandria',
                'departure_time' => '12:00',
                'arrival_time' => '08:00',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['arrival_time']);
        });

        it('validates price is positive', function (): void {
            $bus = Bus::factory()->create();

            $response = $this->post(route('admin.trips.store'), [
                'origin' => 'Cairo',
                'destination' => 'Alexandria',
                'departure_time' => '08:00',
                'arrival_time' => '12:00',
                'price' => -50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['price']);
        });

        it('validates bus exists', function (): void {
            $response = $this->post(route('admin.trips.store'), [
                'origin' => 'Cairo',
                'destination' => 'Alexandria',
                'departure_time' => '08:00',
                'arrival_time' => '12:00',
                'price' => 150.50,
                'bus_id' => 999,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['bus_id']);
        });

        it('validates time format', function (): void {
            $bus = Bus::factory()->create();

            $response = $this->post(route('admin.trips.store'), [
                'origin' => 'Cairo',
                'destination' => 'Alexandria',
                'departure_time' => 'invalid-time',
                'arrival_time' => '12:00',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['departure_time']);
        });
    });

    describe('Trip Editing', function (): void {
        it('can view the trip edit page', function (): void {
            $trip = Trip::factory()->create();
            Bus::factory()->count(3)->create();

            $response = $this->get(route('admin.trips.edit', $trip));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/trips/edit')
                    ->has('trip')
                    ->has('buses')
                    ->where('trip.id', $trip->id)
                );
        });

        it('can update a trip with valid data', function (): void {
            $oldBus = Bus::factory()->create();
            $newBus = Bus::factory()->create();
            $oldOriginCity = City::factory()->create(['name' => 'Old Origin']);
            $oldDestinationCity = City::factory()->create(['name' => 'Old Destination']);
            $newOriginCity = City::factory()->create(['name' => 'New Origin']);
            $newDestinationCity = City::factory()->create(['name' => 'New Destination']);

            $trip = Trip::factory()->create([
                'origin_city_id' => $oldOriginCity->id,
                'destination_city_id' => $oldDestinationCity->id,
                'price' => 100.00,
                'bus_id' => $oldBus->id,
                'is_active' => false,
            ]);

            $updateData = [
                'origin_city_id' => $newOriginCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_time' => '09:00',
                'arrival_time' => '15:00',
                'price' => 200.00,
                'bus_id' => $newBus->id,
                'is_active' => true,
            ];

            $response = $this->patch(route('admin.trips.update', $trip), $updateData);

            $response->assertRedirect(route('admin.trips.index'))
                ->assertSessionHas('success', 'Trip updated successfully.');

            $this->assertDatabaseHas('trips', [
                'id' => $trip->id,
                'origin_city_id' => $newOriginCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'price' => 200.00,
                'bus_id' => $newBus->id,
                'is_active' => true,
            ]);
        });

        it('validates all fields when updating', function (): void {
            $trip = Trip::factory()->create();
            $city = City::factory()->create();

            $response = $this->patch(route('admin.trips.update', $trip), [
                'origin_city_id' => $city->id,
                'destination_city_id' => $city->id, // Should fail - same as origin
                'departure_time' => '15:00',
                'arrival_time' => '10:00', // Should fail - before departure
                'price' => -100, // Should fail - negative
                'bus_id' => 999, // Should fail - doesn't exist
            ]);

            $response->assertSessionHasErrors([
                'destination_city_id',
                'arrival_time',
                'price',
                'bus_id',
            ]);
        });
    });

    describe('Trip Deletion', function (): void {
        it('can delete a trip', function (): void {
            $trip = Trip::factory()->create();

            $response = $this->delete(route('admin.trips.destroy', $trip));

            $response->assertRedirect(route('admin.trips.index'))
                ->assertSessionHas('success', 'Trip deleted successfully.');

            $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
        });

        it('returns 404 when trying to delete non-existent trip', function (): void {
            $response = $this->delete(route('admin.trips.destroy', 999));

            $response->assertStatus(404);
        });
    });

    describe('Trip Time Handling', function (): void {
        it('correctly stores and retrieves time data', function (): void {
            $bus = Bus::factory()->create();
            $originCity = City::factory()->create(['name' => 'Cairo']);
            $destinationCity = City::factory()->create(['name' => 'Alexandria']);

            $tripData = [
                'origin_city_id' => $originCity->id,
                'destination_city_id' => $destinationCity->id,
                'departure_time' => '08:30',
                'arrival_time' => '14:45',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ];

            $this->post(route('admin.trips.store'), $tripData);

            $trip = Trip::first();

            // The time might be stored as full datetime or just time depending on database setup
            expect($trip->departure_time)->toContain('08:30');
            expect($trip->arrival_time)->toContain('14:45');
        });

        it('handles edge cases for time validation', function (): void {
            $bus = Bus::factory()->create();

            // Test same departure and arrival time (should fail)
            $response = $this->post(route('admin.trips.store'), [
                'origin' => 'Cairo',
                'destination' => 'Alexandria',
                'departure_time' => '12:00',
                'arrival_time' => '12:00',
                'price' => 150.50,
                'bus_id' => $bus->id,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['arrival_time']);
        });
    });

    describe('Authorization', function (): void {
        it('requires admin role for all trip operations', function (): void {
            // Create a regular user
            $user = User::factory()->create(['role' => Role::USER]);
            $this->actingAs($user);

            $trip = Trip::factory()->create();

            // Test all routes require admin access
            $this->get(route('admin.trips.index'))->assertStatus(403);
            $this->get(route('admin.trips.create'))->assertStatus(403);
            $this->post(route('admin.trips.store'), [])->assertStatus(403);
            $this->get(route('admin.trips.edit', $trip))->assertStatus(403);
            $this->patch(route('admin.trips.update', $trip), [])->assertStatus(403);
            $this->delete(route('admin.trips.destroy', $trip))->assertStatus(403);
        });

        it('requires authentication for all trip operations', function (): void {
            // Logout
            auth()->logout();

            $trip = Trip::factory()->create();

            // Test all routes require authentication
            $this->get(route('admin.trips.index'))->assertRedirect(route('login'));
            $this->get(route('admin.trips.create'))->assertRedirect(route('login'));
            $this->post(route('admin.trips.store'), [])->assertRedirect(route('login'));
            $this->get(route('admin.trips.edit', $trip))->assertRedirect(route('login'));
            $this->patch(route('admin.trips.update', $trip), [])->assertRedirect(route('login'));
            $this->delete(route('admin.trips.destroy', $trip))->assertRedirect(route('login'));
        });
    });

    describe('Business Logic', function (): void {
        it('displays trips with associated bus information', function (): void {
            $bus = Bus::factory()->create(['bus_code' => 'LUXURY001', 'capacity' => 45]);
            Trip::factory()->forBus($bus)->create();

            $response = $this->get(route('admin.trips.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->has('trips.data.0.bus')
                    ->where('trips.data.0.bus.bus_code', 'LUXURY001')
                    ->where('trips.data.0.bus.capacity', 45)
                );
        });

        it('orders trips by departure time by default', function (): void {
            $trip1 = Trip::factory()->withTimes('15:00', '18:00')->create();
            $trip2 = Trip::factory()->withTimes('08:00', '12:00')->create();
            $trip3 = Trip::factory()->withTimes('12:00', '16:00')->create();

            $response = $this->get(route('admin.trips.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->where('trips.data.0.id', $trip2->id) // 08:00 first
                    ->where('trips.data.1.id', $trip3->id) // 12:00 second
                    ->where('trips.data.2.id', $trip1->id) // 15:00 third
                );
        });
    });
});
