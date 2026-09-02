<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side gate for every /admin route.
 *
 * Hiding admin links in Blade is *not* a security control — this middleware is
 * what actually keeps normal users out of the administration panel.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact the organisation.');
        }

        abort_unless($user->isAdmin(), 403, 'This area is restricted to the KUET TRY administrator.');

        return $next($request);
    }
}
