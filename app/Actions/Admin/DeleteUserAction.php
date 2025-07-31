<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;

/**
 * Action for admin user deletion.
 */
final class DeleteUserAction
{
    /**
     * Delete a user's account as admin.
     */
    public function execute(User $user): void
    {
        $user->delete();
    }
}
