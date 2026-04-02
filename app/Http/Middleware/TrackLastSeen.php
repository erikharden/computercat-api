<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Update authenticated user's last_seen_at timestamp.
 *
 * Throttled to once per hour to avoid excessive DB writes.
 */
class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->last_seen_at || $user->last_seen_at->diffInMinutes(now()) >= 60)) {
            $user->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
