<?php

namespace Atmos\DbSentinel\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class AuthorizeSentinel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIds = config('db-sentinel.dashboard.security.authorized_user_ids', []);

        // If null or empty array, everyone can access
        if (empty($allowedIds)) {
            return $next($request);
        }

        // Check if current user ID is in the allowed list
        if (!auth()->check() || !in_array(auth()->id(), $allowedIds)) {
            abort(403);
        }

        return $next($request);
    }
}
