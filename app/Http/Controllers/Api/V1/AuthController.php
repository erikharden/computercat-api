<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function anonymous(Request $request): JsonResponse
    {
        $user = User::create([
            'name' => 'Player-'.Str::random(8),
            'display_name' => $request->input('display_name'),
            'is_anonymous' => true,
        ]);

        $token = $user->createToken('anonymous')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'display_name' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        if ($user && $user->is_anonymous) {
            // Upgrade anonymous account
            $user->update([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'display_name' => $validated['display_name'] ?? $user->display_name,
                'is_anonymous' => false,
            ]);
        } else {
            // Create new account
            $user = User::create([
                'name' => $validated['display_name'] ?? 'Player-'.Str::random(8),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'display_name' => $validated['display_name'],
                'is_anonymous' => false,
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($user->is_banned) {
            return response()->json(['message' => 'Account is banned.'], 403);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:50',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}
