<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\RemoteConfig;
use Illuminate\Http\JsonResponse;

class RemoteConfigController extends Controller
{
    public function index(Game $game): JsonResponse
    {
        $configs = RemoteConfig::where('game_id', $game->id)
            ->where('is_active', true)
            ->get();

        $data = [];
        foreach ($configs as $config) {
            $data[$config->key] = $config->typedValue();
        }

        return response()->json(['data' => $data]);
    }
}
