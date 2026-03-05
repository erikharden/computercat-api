<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGame
{
    public function handle(Request $request, Closure $next): Response
    {
        $gameSlug = $request->route('game');

        if (is_string($gameSlug)) {
            $game = Game::where('slug', $gameSlug)->where('is_active', true)->first();

            if (! $game) {
                return response()->json(['message' => 'Game not found.'], 404);
            }

            $request->route()->setParameter('game', $game);
        }

        return $next($request);
    }
}
