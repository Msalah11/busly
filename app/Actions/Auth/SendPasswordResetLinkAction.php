<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

/**
 * Action for sending password reset links.
 */
final class SendPasswordResetLinkAction
{
    /**
     * Send a password reset link to the given email.
     */
    public function execute(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }
}
