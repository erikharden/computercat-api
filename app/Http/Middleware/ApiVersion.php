<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds API version headers to responses.
 *
 * When v2 is needed, duplicate the route group under /v2 prefix
 * and use this middleware to tag responses with the version.
 * Clients can check the X-API-Version header to detect mismatches.
 */
class ApiVersion
{
    public function handle(Request $request, Closure $next, string $version = '1'): Response
    {
        $response = $next($request);

        $response->headers->set('X-API-Version', $version);
        $response->headers->set('X-API-Deprecation', 'false');

        return $response;
    }
}
