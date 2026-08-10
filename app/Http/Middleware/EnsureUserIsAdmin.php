<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Allow only admin role through (API + web).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Support\ApiErrorResponse::make(
                    'Admin access required.',
                    403,
                    'FORBIDDEN'
                );
            }

            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
