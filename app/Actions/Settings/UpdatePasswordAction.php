<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DTOs\Settings\PasswordUpdateData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Action for updating user passwords.
 */
final class UpdatePasswordAction
{
    /**
     * Update a user's password.
     */
    public function execute(User $user, PasswordUpdateData $data): User
    {
        $user->update([
            'password' => Hash::make($data->password),
        ]);

        return $user->fresh();
    }
}
