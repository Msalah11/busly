<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Show the password reset page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(PasswordResetRequest $request, ResetPasswordAction $action): RedirectResponse
    {
        $passwordResetData = $request->toDTO();

        $action->execute($passwordResetData);

        return to_route('login')->with('status', __(Password::PasswordReset));
    }
}
