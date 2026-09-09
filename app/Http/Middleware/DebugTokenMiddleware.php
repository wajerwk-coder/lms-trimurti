<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to diagnostic/debug endpoints.
 *
 * Allows the request through when:
 * - the application is running in the 'local' environment, OR
 * - a valid debug token is supplied via the 'X-Debug-Token' header
 *   or the 'debug_token' query parameter, matching config('app.debug_token')
 *   (sourced from the DEBUG_TOKEN environment variable).
 *
 * Otherwise responds with 404 to avoid leaking the existence of the route.
 */
class DebugTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        $expectedToken = config('app.debug_token') ?: env('DEBUG_TOKEN');
        $providedToken = $request->header('X-Debug-Token') ?? $request->query('debug_token');

        if (!empty($expectedToken) && hash_equals((string) $expectedToken, (string) $providedToken)) {
            return $next($request);
        }

        abort(404);
    }
}
