<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteUserAction;
use App\Actions\Admin\GetUsersListAction;
use App\Actions\Admin\UpdateUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserCreateRequest;
use App\Http\Requests\Admin\AdminUsersRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for admin user management operations.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(AdminUsersRequest $request, GetUsersListAction $action): Response
    {
        $listData = $request->toDTO();
        $users = $action->execute($listData);

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $listData->search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(AdminUserCreateRequest $request, RegisterUserAction $action): RedirectResponse
    {
        $registerData = $request->toDTO();
        $action->execute($registerData, autoLogin: false);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(AdminUserUpdateRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $profileData = $request->toDTO();
        $action->execute($user, $profileData, $request);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user, DeleteUserAction $action): RedirectResponse
    {
        // Prevent deleting the current admin user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $action->execute($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
