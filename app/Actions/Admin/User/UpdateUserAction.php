<?php

declare(strict_types=1);

namespace App\Actions\Admin\User;

use App\DTOs\Settings\ProfileUpdateData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Action for admin user updates.
 */
final class UpdateUserAction
{
    /**
     * Update a user's profile information as admin.
     */
    public function execute(User $user, ProfileUpdateData $data, Request $request): User
    {
        $user->fill($data->toArray());

        // Handle password update if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->string('password')->toString());
        }

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->fresh();
    }
}
