<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Handles webhook events from RevenueCat.
 *
 * RevenueCat sends events when purchases happen, renew, cancel, or refund.
 * We use these events to keep our Purchase table in sync with the source of truth.
 *
 * Signature verification:
 * RevenueCat sends the webhook secret as a Bearer token in the Authorization header.
 * We compare it to the encrypted secret stored in games.settings.revenuecat.webhook_secret.
 *
 * Docs: https://www.revenuecat.com/docs/webhooks
 */
class RevenueCatWebhookController extends Controller
{
    public function handle(Request $request, Game $game): JsonResponse
    {
        // Verify Authorization header — accept both "Bearer <secret>" and raw "<secret>"
        // since RevenueCat sends whatever you put in the dashboard verbatim.
        $authHeader = $request->header('Authorization');
        if (! $authHeader) {
            return response()->json(['message' => 'Missing authorization.'], 401);
        }

        $providedToken = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : $authHeader;

        // Get the configured webhook secret for this game
        $encryptedSecret = $game->settings['revenuecat']['webhook_secret'] ?? null;
        if (! $encryptedSecret) {
            Log::warning("RevenueCat webhook received for {$game->slug} but no secret configured");

            return response()->json(['message' => 'Webhook not configured for this game.'], 501);
        }

        try {
            $expectedSecret = Crypt::decryptString($encryptedSecret);
        } catch (\Throwable $e) {
            Log::error("Failed to decrypt RevenueCat webhook secret for {$game->slug}", ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Configuration error.'], 500);
        }

        if (! hash_equals($expectedSecret, $providedToken)) {
            Log::warning("Invalid RevenueCat webhook signature for {$game->slug}");

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        // Parse event
        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;

        if (! $event) {
            return response()->json(['message' => 'Invalid payload.'], 422);
        }

        $type = $event['type'] ?? null;
        $appUserId = $event['app_user_id'] ?? null;
        $productId = $event['product_id'] ?? null;
        $transactionId = $event['transaction_id'] ?? null;
        $store = $this->mapStore($event['store'] ?? null);

        if (! $type || ! $appUserId || ! $productId || ! $transactionId) {
            return response()->json(['message' => 'Missing required event fields.'], 422);
        }

        // Resolve user from app_user_id (we set this to our numeric user ID in the client)
        $user = User::find((int) $appUserId);
        if (! $user) {
            Log::warning("RevenueCat webhook for unknown user", ['app_user_id' => $appUserId, 'type' => $type]);

            // Return 200 to prevent RC from retrying indefinitely
            return response()->json(['message' => 'User not found, ignoring.'], 200);
        }

        $this->processEvent($type, $user, $game, $productId, $transactionId, $store, $event);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Process a RevenueCat event by upserting/updating the Purchase record.
     */
    private function processEvent(
        string $type,
        User $user,
        Game $game,
        string $productId,
        string $transactionId,
        string $store,
        array $event,
    ): void {
        $purchasedAt = isset($event['purchased_at_ms'])
            ? now()->createFromTimestampMs((int) $event['purchased_at_ms'])
            : now();

        switch ($type) {
            case 'INITIAL_PURCHASE':
            case 'NON_RENEWING_PURCHASE':
            case 'RENEWAL':
            case 'UNCANCELLATION':
                // Grant access
                Purchase::updateOrCreate(
                    [
                        'transaction_id' => $transactionId,
                    ],
                    [
                        'user_id' => $user->id,
                        'game_id' => $game->id,
                        'product_id' => $productId,
                        'store' => $store,
                        'receipt_data' => json_encode($event),
                        'status' => 'verified',
                        'purchased_at' => $purchasedAt,
                    ]
                );
                break;

            case 'CANCELLATION':
            case 'EXPIRATION':
                // Mark as expired but don't revoke (user had access during the period)
                // For non-subscription purchases this shouldn't fire
                Purchase::where('transaction_id', $transactionId)
                    ->update(['status' => 'pending']); // grace — still owned
                break;

            case 'REFUND':
                // Revoke access
                Purchase::where('transaction_id', $transactionId)
                    ->update(['status' => 'refunded']);
                break;

            case 'TEST':
                // Test event from RevenueCat dashboard — just log
                Log::info("RevenueCat test webhook received for {$game->slug}");
                break;

            default:
                Log::info("Unhandled RevenueCat event type: {$type}");
        }
    }

    /**
     * Map RevenueCat store identifiers to our enum.
     */
    private function mapStore(?string $rcStore): string
    {
        return match ($rcStore) {
            'APP_STORE', 'MAC_APP_STORE' => 'apple',
            'PLAY_STORE' => 'google',
            'STRIPE', 'PROMOTIONAL' => 'web',
            default => 'web',
        };
    }
}
