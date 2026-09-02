<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordUpdateRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    /**
     * Change the signed-in user's password. Requires the current password, and
     * refreshes the session so other sessions are not silently kept alive.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        // Keep this session signed in, invalidate any other one.
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            $this->activity->log('admin.password_changed', 'Changed the administrator password');
        }

        $route = $user->isAdmin() ? 'admin.password.edit' : 'profile.edit';

        return redirect()->route($route)->with('success', 'Your password has been updated.');
    }
}
