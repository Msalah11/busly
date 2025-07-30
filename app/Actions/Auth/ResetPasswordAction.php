<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\PasswordResetData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Action for resetting user passwords.
 */
final class ResetPasswordAction
{
    /**
     * Reset a user's password using the reset token.
     *
     * @throws ValidationException If reset fails
     */
    public function execute(PasswordResetData $data): void
    {
        $status = Password::reset(
            $data->toArray(),
            function (User $user) use ($data): void {
                $user->forceFill([
                    'password' => Hash::make($data->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
