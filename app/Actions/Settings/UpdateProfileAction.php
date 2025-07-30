<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DTOs\Settings\ProfileUpdateData;
use App\Models\User;

/**
 * Action for updating user profiles.
 */
final class UpdateProfileAction
{
    /**
     * Update a user's profile information.
     */
    public function execute(User $user, ProfileUpdateData $data): User
    {
        $user->fill($data->toArray());

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->fresh();
    }
}
