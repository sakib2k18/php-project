<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Create a supporter account.
     *
     * Every attribute is assigned explicitly — there is no `User::create($request->all())`
     * anywhere in this application — so `role` stays at its database default of
     * 'user' no matter what the request body contains.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->student_id = $validated['student_id'] ?? null;
        $user->password = Hash::make($validated['password']);
        $user->role = User::ROLE_USER;   // never taken from the request
        $user->is_active = true;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        // Guard against session fixation on privilege change.
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to '.settings()->name().", {$user->name}! Your account is ready.");
    }
}
