<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Action for registering new users.
 */
final class RegisterUserAction
{
    /**
     * Register a new user and optionally log them in.
     */
    public function execute(RegisterData $data, bool $autoLogin = true): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'role' => $data->role,
        ]);

        event(new Registered($user));

        if ($autoLogin) {
            Auth::login($user);
        }

        return $user;
    }
}
