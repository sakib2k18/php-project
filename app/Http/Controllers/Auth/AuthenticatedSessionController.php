<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Log the user in. `remember` uses Laravel's own remember-me cookie, which
     * stores a rotating token — never the password.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // New session ID after a privilege change (session fixation defence).
        $request->session()->regenerate();

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        $home = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return redirect()->intended($home)
            ->with('success', "Welcome back, {$user->name}.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
