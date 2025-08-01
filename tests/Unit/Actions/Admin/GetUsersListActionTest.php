<?php

declare(strict_types=1);

use App\Actions\Admin\User\GetUsersListAction;
use App\DTOs\Admin\User\AdminUserListData;
use App\Enums\Role;
use App\Models\User;

describe('GetUsersListAction', function (): void {
    beforeEach(function (): void {
        $this->action = new GetUsersListAction;
    });

    it('can get users list with default parameters', function (): void {
        User::factory()->count(5)->create();

        $data = new AdminUserListData;
        $result = $this->action->execute($data);

        expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
        expect($result->count())->toBe(5);
        expect($result->perPage())->toBe(15);
    });

    it('can search users by name', function (): void {
        User::factory()->create(['name' => 'john doe']);
        User::factory()->create(['name' => 'jane smith']);
        User::factory()->create(['name' => 'bob johnson']);

        $data = new AdminUserListData(search: 'john');
        $result = $this->action->execute($data);

        expect($result->count())->toBe(2); // john doe and bob johnson
    });

    it('can search users by email', function (): void {
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@test.com']);
        User::factory()->create(['email' => 'admin@example.com']);

        $data = new AdminUserListData(search: 'example');
        $result = $this->action->execute($data);

        expect($result->count())->toBe(2); // john@example.com and admin@example.com
    });

    it('can customize per page and page number', function (): void {
        User::factory()->count(25)->create();

        $data = new AdminUserListData(perPage: 10, page: 2);
        $result = $this->action->execute($data);

        expect($result->perPage())->toBe(10);
        expect($result->currentPage())->toBe(2);
        expect($result->count())->toBe(10);
    });

    it('returns users with correct columns', function (): void {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => Role::ADMIN,
        ]);

        $data = new AdminUserListData;
        $result = $this->action->execute($data);

        $user = $result->first();
        expect($user)->toHaveKey('id');
        expect($user)->toHaveKey('name');
        expect($user)->toHaveKey('email');
        expect($user)->toHaveKey('role');
        expect($user)->toHaveKey('email_verified_at');
        expect($user)->toHaveKey('created_at');
    });

    it('can get dashboard data', function (): void {
        User::factory()->count(15)->create();

        $result = $this->action->getDashboardData();

        expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
        expect($result->perPage())->toBe(10);
        expect($result->count())->toBe(10); // Limited to 10 for dashboard
        expect($result->total())->toBe(15);
    });

    it('orders users by creation date descending', function (): void {
        $oldUser = User::factory()->create(['created_at' => now()->subDays(2)]);
        $newUser = User::factory()->create(['created_at' => now()]);

        $data = new AdminUserListData;
        $result = $this->action->execute($data);

        expect($result->first()->id)->toBe($newUser->id);
        expect($result->last()->id)->toBe($oldUser->id);
    });
});
