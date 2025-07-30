<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\DeleteUserAction;
use App\Actions\Settings\UpdateProfileAction;
use App\DTOs\Settings\ProfileUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'user' => $request->user(),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $profileData = ProfileUpdateData::fromRequest($request);

        $action->execute($request->user(), $profileData);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, DeleteUserAction $action): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $action->execute($request->user(), $request);

        return redirect('/');
    }
}
