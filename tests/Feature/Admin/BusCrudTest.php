<?php

declare(strict_types=1);

use App\Enums\BusType;
use App\Enums\Role;
use App\Models\Bus;
use App\Models\User;

describe('Admin Bus CRUD Operations', function (): void {
    beforeEach(function (): void {
        // Create an admin user for each test
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    describe('Bus Index/List', function (): void {
        it('can view the bus index page', function (): void {
            $response = $this->get(route('admin.buses.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/index')
                    ->has('buses')
                    ->has('flash')
                );
        });

        it('displays buses with pagination', function (): void {
            Bus::factory()->count(25)->create();

            $response = $this->get(route('admin.buses.index'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/index')
                    ->has('buses.data', 15) // Default pagination
                    ->has('buses.links')
                    ->where('buses.total', 25)
                );
        });

        it('can filter buses by search term', function (): void {
            $bus1 = Bus::factory()->create(['bus_code' => 'ABC123']);
            $bus2 = Bus::factory()->create(['bus_code' => 'XYZ789']);

            $response = $this->get(route('admin.buses.index', ['search' => 'ABC123']));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/index')
                    ->has('buses.data', 1)
                    ->where('buses.data.0.bus_code', 'ABC123')
                );
        });

        it('can filter buses by type', function (): void {
            Bus::factory()->vip()->count(3)->create();
            Bus::factory()->standard()->count(2)->create();

            $response = $this->get(route('admin.buses.index', ['type' => BusType::VIP->value]));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/index')
                    ->has('buses.data', 3)
                    ->where('buses.data.0.type', BusType::VIP->value)
                );
        });

        it('can filter buses by active status', function (): void {
            Bus::factory()->active()->count(3)->create();
            Bus::factory()->inactive()->count(2)->create();

            $response = $this->get(route('admin.buses.index', ['active' => true]));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/index')
                    ->has('buses.data', 3)
                );
        });
    });

    describe('Bus Creation', function (): void {
        it('can view the bus creation page', function (): void {
            $response = $this->get(route('admin.buses.create'));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/create')
                );
        });

        it('can create a new bus with valid data', function (): void {
            $busData = [
                'bus_code' => 'TEST123',
                'capacity' => 50,
                'type' => BusType::STANDARD->value,
                'is_active' => true,
            ];

            $response = $this->post(route('admin.buses.store'), $busData);

            $response->assertRedirect(route('admin.buses.index'))
                ->assertSessionHas('success', 'Bus created successfully.');

            $this->assertDatabaseHas('buses', [
                'bus_code' => 'TEST123',
                'capacity' => 50,
                'type' => BusType::STANDARD,
                'is_active' => true,
            ]);
        });

        it('validates required fields when creating a bus', function (): void {
            $response = $this->post(route('admin.buses.store'), []);

            $response->assertSessionHasErrors([
                'bus_code',
                'capacity',
                'type',
            ]);
        });

        it('validates bus_code uniqueness', function (): void {
            Bus::factory()->create(['bus_code' => 'DUPLICATE123']);

            $response = $this->post(route('admin.buses.store'), [
                'bus_code' => 'DUPLICATE123',
                'capacity' => 50,
                'type' => BusType::STANDARD->value,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['bus_code']);
        });

        it('validates capacity is a positive integer', function (): void {
            $response = $this->post(route('admin.buses.store'), [
                'bus_code' => 'TEST123',
                'capacity' => -10,
                'type' => BusType::STANDARD->value,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['capacity']);
        });

        it('validates type is a valid enum value', function (): void {
            $response = $this->post(route('admin.buses.store'), [
                'bus_code' => 'TEST123',
                'capacity' => 50,
                'type' => 'invalid_type',
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['type']);
        });
    });

    describe('Bus Editing', function (): void {
        it('can view the bus edit page', function (): void {
            $bus = Bus::factory()->create();

            $response = $this->get(route('admin.buses.edit', $bus));

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page
                    ->component('admin/buses/edit')
                    ->has('bus')
                    ->where('bus.id', $bus->id)
                );
        });

        it('can update a bus with valid data', function (): void {
            $bus = Bus::factory()->create([
                'bus_code' => 'OLD123',
                'capacity' => 40,
                'type' => BusType::STANDARD,
                'is_active' => false,
            ]);

            $updateData = [
                'bus_code' => 'NEW123',
                'capacity' => 60,
                'type' => BusType::VIP->value,
                'is_active' => true,
            ];

            $response = $this->patch(route('admin.buses.update', $bus), $updateData);

            $response->assertRedirect(route('admin.buses.index'))
                ->assertSessionHas('success', 'Bus updated successfully.');

            $this->assertDatabaseHas('buses', [
                'id' => $bus->id,
                'bus_code' => 'NEW123',
                'capacity' => 60,
                'type' => BusType::VIP,
                'is_active' => true,
            ]);
        });

        it('validates unique bus_code when updating (excluding current bus)', function (): void {
            $bus1 = Bus::factory()->create(['bus_code' => 'BUS001']);
            $bus2 = Bus::factory()->create(['bus_code' => 'BUS002']);

            // Should fail - trying to use another bus's code
            $response = $this->patch(route('admin.buses.update', $bus1), [
                'bus_code' => 'BUS002',
                'capacity' => 50,
                'type' => BusType::STANDARD->value,
                'is_active' => true,
            ]);

            $response->assertSessionHasErrors(['bus_code']);

            // Should succeed - keeping the same code
            $response = $this->patch(route('admin.buses.update', $bus1), [
                'bus_code' => 'BUS001',
                'capacity' => 50,
                'type' => BusType::STANDARD->value,
                'is_active' => true,
            ]);

            $response->assertRedirect(route('admin.buses.index'))
                ->assertSessionHasNoErrors();
        });
    });

    describe('Bus Deletion', function (): void {
        it('can delete a bus', function (): void {
            $bus = Bus::factory()->create();

            $response = $this->delete(route('admin.buses.destroy', $bus));

            $response->assertRedirect(route('admin.buses.index'))
                ->assertSessionHas('success', 'Bus deleted successfully.');

            $this->assertDatabaseMissing('buses', ['id' => $bus->id]);
        });

        it('returns 404 when trying to delete non-existent bus', function (): void {
            $response = $this->delete(route('admin.buses.destroy', 999));

            $response->assertStatus(404);
        });

        it('prevents deletion of bus with active trips', function (): void {
            $bus = Bus::factory()->has(\App\Models\Trip::factory()->active()->count(2))->create();

            $response = $this->delete(route('admin.buses.destroy', $bus));

            $response->assertRedirect(route('admin.buses.index'))
                ->assertSessionHas('error', 'Cannot delete bus with active trips.');

            $this->assertDatabaseHas('buses', ['id' => $bus->id]);
        });
    });

    describe('Authorization', function (): void {
        it('requires admin role for all bus operations', function (): void {
            // Create a regular user
            $user = User::factory()->create(['role' => Role::USER]);
            $this->actingAs($user);

            $bus = Bus::factory()->create();

            // Test all routes require admin access
            $this->get(route('admin.buses.index'))->assertStatus(403);
            $this->get(route('admin.buses.create'))->assertStatus(403);
            $this->post(route('admin.buses.store'), [])->assertStatus(403);
            $this->get(route('admin.buses.edit', $bus))->assertStatus(403);
            $this->patch(route('admin.buses.update', $bus), [])->assertStatus(403);
            $this->delete(route('admin.buses.destroy', $bus))->assertStatus(403);
        });

        it('requires authentication for all bus operations', function (): void {
            // Logout
            auth()->logout();

            $bus = Bus::factory()->create();

            // Test all routes require authentication
            $this->get(route('admin.buses.index'))->assertRedirect(route('login'));
            $this->get(route('admin.buses.create'))->assertRedirect(route('login'));
            $this->post(route('admin.buses.store'), [])->assertRedirect(route('login'));
            $this->get(route('admin.buses.edit', $bus))->assertRedirect(route('login'));
            $this->patch(route('admin.buses.update', $bus), [])->assertRedirect(route('login'));
            $this->delete(route('admin.buses.destroy', $bus))->assertRedirect(route('login'));
        });
    });
});
