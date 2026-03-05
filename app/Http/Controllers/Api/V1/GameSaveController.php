<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GameSaveResource;
use App\Models\Game;
use App\Models\GameSave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameSaveController extends Controller
{
    public function index(Request $request, Game $game): JsonResponse
    {
        $saves = GameSave::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->get();

        return response()->json([
            'data' => GameSaveResource::collection($saves),
        ]);
    }

    public function show(Request $request, Game $game, string $key): JsonResponse
    {
        $save = GameSave::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('save_key', $key)
            ->first();

        if (! $save) {
            return response()->json(['message' => 'Save not found.'], 404);
        }

        return response()->json([
            'data' => new GameSaveResource($save),
        ]);
    }

    public function update(Request $request, Game $game, string $key): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'version' => 'required|integer|min:1',
            'checksum' => 'nullable|string|max:64',
        ]);

        $save = GameSave::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('save_key', $key)
            ->first();

        if ($save) {
            // Optimistic locking: client must send current version
            if ($validated['version'] !== $save->version) {
                return response()->json([
                    'message' => 'Version conflict. Fetch latest save and merge.',
                    'server_version' => $save->version,
                    'data' => new GameSaveResource($save),
                ], 409);
            }

            $save->update([
                'data' => $validated['data'],
                'version' => $save->version + 1,
                'checksum' => $validated['checksum'] ?? null,
                'saved_at' => now(),
            ]);
        } else {
            $save = GameSave::create([
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
                'save_key' => $key,
                'data' => $validated['data'],
                'version' => 1,
                'checksum' => $validated['checksum'] ?? null,
                'saved_at' => now(),
            ]);
        }

        return response()->json([
            'data' => new GameSaveResource($save->fresh()),
        ]);
    }

    public function destroy(Request $request, Game $game, string $key): JsonResponse
    {
        $deleted = GameSave::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('save_key', $key)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Save not found.'], 404);
        }

        return response()->json(['message' => 'Save deleted.']);
    }
}
