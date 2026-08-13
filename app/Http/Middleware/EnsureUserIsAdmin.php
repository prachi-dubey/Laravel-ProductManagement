<?php

namespace App\Http\Middleware;

use App\Helper\ApiErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiErrorResponse::make(
                    __('messages.auth.admin_required'),
                    403,
                    'FORBIDDEN'
                );
            }

            abort(403, __('messages.auth.admin_required'));
        }

        return $next($request);
    }
}
