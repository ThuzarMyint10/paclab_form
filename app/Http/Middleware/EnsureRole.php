<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:admin,staff') or ->middleware('role:customer')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(
                $request->is('admin', 'admin/*') ? route('admin.login') : route('login')
            );
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated. Please contact Pacific Lab.']);
        }

        if (! in_array($user->role, $roles, true)) {
            // Send people to the area they belong to rather than showing an error
            return $user->isStaff()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
