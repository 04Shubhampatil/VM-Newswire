<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admins created with a one-time password must set their own before using the panel.
 */
class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('admin.account.*', 'admin.logout')) {
            return redirect()->route('admin.account.password')
                ->with('toast', 'Please set a new password before continuing.');
        }

        return $next($request);
    }
}
