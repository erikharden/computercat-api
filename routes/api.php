<?php

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\GameSaveController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use App\Http\Controllers\Api\V1\OwnershipController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Middleware\ApiVersion;
use App\Http\Middleware\ResolveGame;
use App\Http\Middleware\TrackLastSeen;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(ApiVersion::class.':1')->group(function () {
    // Auth (public)
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/anonymous', [AuthController::class, 'anonymous']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    // Auth (authenticated, allows anonymous upgrade)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::patch('/auth/me', [AuthController::class, 'update']);
        Route::delete('/auth/me', [AuthController::class, 'destroy']);
    });

    // Games (public)
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{game:slug}', [GameController::class, 'show']);

    // Game-scoped endpoints (authenticated)
    Route::middleware(['auth:sanctum', TrackLastSeen::class, ResolveGame::class])
        ->prefix('/games/{game}')
        ->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)
        ->group(function () {
            // Leaderboards
            Route::get('/leaderboards/{type}/me', [LeaderboardController::class, 'me']);
            Route::get('/leaderboards/{type}', [LeaderboardController::class, 'index']);
            Route::get('/leaderboards/{type}/{periodKey}', [LeaderboardController::class, 'show']);
            Route::post('/leaderboards/{type}', [LeaderboardController::class, 'store'])
                ->middleware('throttle:score-submit');

            // Achievements
            Route::get('/achievements', [AchievementController::class, 'index']);
            Route::get('/achievements/me', [AchievementController::class, 'me']);
            Route::post('/achievements', [AchievementController::class, 'store']);

            // Saves
            Route::get('/saves', [GameSaveController::class, 'index']);
            Route::get('/saves/{key}', [GameSaveController::class, 'show']);
            Route::put('/saves/{key}', [GameSaveController::class, 'update']);
            Route::delete('/saves/{key}', [GameSaveController::class, 'destroy']);

            // Ownership (server-authoritative)
            Route::get('/ownership', [OwnershipController::class, 'show']);
        });

    // Purchases (authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index']);
        Route::post('/purchases/verify', [PurchaseController::class, 'verify']);
    });
});
