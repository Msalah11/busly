<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginData;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Action for authenticating users.
 */
final class LoginUserAction
{
    /**
     * Authenticate a user with the given credentials.
     *
     * @throws ValidationException If authentication fails or rate limited
     */
    public function execute(LoginData $data, Request $request): void
    {
        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($data->toArray(), $data->remember)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException If rate limited
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->string('email')).'|'.$request->ip());
    }
}
