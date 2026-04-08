<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for App Store Connect API v2.
 *
 * Docs: https://developer.apple.com/documentation/appstoreconnectapi
 */
class AppStoreConnectClient
{
    private const BASE_URL = 'https://api.appstoreconnect.apple.com';

    private Game $game;

    private string $keyId;

    private string $issuerId;

    private string $privateKey;

    public string $appAppleId;

    public function __construct(Game $game)
    {
        $settings = $game->settings['app_store_connect'] ?? [];

        if (empty($settings['issuer_id']) || empty($settings['key_id']) || empty($settings['private_key']) || empty($settings['app_apple_id'])) {
            throw new RuntimeException("App Store Connect API not configured for game {$game->slug}. Set issuer_id, key_id, private_key, and app_apple_id in game settings.");
        }

        $this->game = $game;
        $this->keyId = $settings['key_id'];
        $this->issuerId = $settings['issuer_id'];
        $this->privateKey = Crypt::decryptString($settings['private_key']);
        $this->appAppleId = $settings['app_apple_id'];
    }

    /**
     * Get a shared HTTP client with JWT authentication.
     */
    public function http(): PendingRequest
    {
        return Http::withToken($this->generateJwt())
            ->acceptJson()
            ->timeout(30)
            ->throw(function (Response $response, $e) {
                throw new RuntimeException(
                    "App Store Connect API error ({$response->status()}): ".$response->body(),
                    $response->status(),
                    $e,
                );
            });
    }

    /**
     * Find an existing in-app purchase by product ID (vendor ID).
     * Returns the IAP resource or null if not found.
     */
    public function findInAppPurchase(string $productId): ?array
    {
        // Apps have an inAppPurchasesV2 relationship that lists IAPs (v1 endpoint)
        $response = $this->http()->get(self::BASE_URL."/v1/apps/{$this->appAppleId}/inAppPurchasesV2", [
            'filter[productId]' => $productId,
            'limit' => 1,
        ]);

        $data = $response->json('data', []);

        return $data[0] ?? null;
    }

    /**
     * Create a new in-app purchase using the v2 API.
     * v1 is read-only; v2 supports CREATE.
     */
    public function createInAppPurchase(array $attributes): array
    {
        $response = $this->http()->post(self::BASE_URL.'/v2/inAppPurchases', [
            'data' => [
                'type' => 'inAppPurchases',
                'attributes' => $attributes,
                'relationships' => [
                    'app' => [
                        'data' => ['type' => 'apps', 'id' => $this->appAppleId],
                    ],
                ],
            ],
        ]);

        return $response->json('data');
    }

    /**
     * Add a localization (display name + description) to an IAP.
     */
    public function createLocalization(string $iapId, string $locale, string $name, string $description): array
    {
        $response = $this->http()->post(self::BASE_URL.'/v1/inAppPurchaseLocalizations', [
            'data' => [
                'type' => 'inAppPurchaseLocalizations',
                'attributes' => [
                    'locale' => $locale,
                    'name' => $name,
                    'description' => $description,
                ],
                'relationships' => [
                    'inAppPurchaseV2' => [
                        'data' => ['type' => 'inAppPurchases', 'id' => $iapId],
                    ],
                ],
            ],
        ]);

        return $response->json('data');
    }

    /**
     * Find the Apple price point ID closest to the desired price for a territory.
     * Apple only accepts pre-defined price tiers — you can't set arbitrary prices.
     *
     * @param  string  $iapId  In-app purchase ID
     * @param  string  $territory  3-letter territory code (e.g. "SWE")
     * @param  float  $desiredPrice  Target customer price in that territory
     * @return string|null Price point ID (null if no match found)
     */
    public function findPricePoint(string $iapId, string $territory, float $desiredPrice): ?string
    {
        // Query price points for this IAP + territory
        // Apple paginates — we may need multiple pages, but price points are
        // usually few enough to fit in one page
        $response = $this->http()->get(self::BASE_URL."/v2/inAppPurchases/{$iapId}/pricePoints", [
            'filter[territory]' => $territory,
            'limit' => 200,
        ]);

        $points = $response->json('data', []);

        $closestId = null;
        $closestDiff = PHP_FLOAT_MAX;

        foreach ($points as $point) {
            $price = (float) ($point['attributes']['customerPrice'] ?? 0);
            $diff = abs($price - $desiredPrice);

            // Exact match wins immediately
            if ($diff < 0.01) {
                return $point['id'];
            }

            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closestId = $point['id'];
            }
        }

        return $closestId;
    }

    /**
     * Create a price schedule for an IAP with base territory auto-conversion.
     * Apple derives prices for all other territories from the base.
     */
    public function createPriceSchedule(string $iapId, string $baseTerritory, string $pricePointId): array
    {
        $response = $this->http()->post(self::BASE_URL.'/v1/inAppPurchasePriceSchedules', [
            'data' => [
                'type' => 'inAppPurchasePriceSchedules',
                'relationships' => [
                    'inAppPurchase' => [
                        'data' => ['type' => 'inAppPurchases', 'id' => $iapId],
                    ],
                    'baseTerritory' => [
                        'data' => ['type' => 'territories', 'id' => $baseTerritory],
                    ],
                    'manualPrices' => [
                        'data' => [['type' => 'inAppPurchasePrices', 'id' => '${new-price}']],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'inAppPurchasePrices',
                    'id' => '${new-price}',
                    'attributes' => [
                        'startDate' => null,
                    ],
                    'relationships' => [
                        'inAppPurchasePricePoint' => [
                            'data' => ['type' => 'inAppPurchasePricePoints', 'id' => $pricePointId],
                        ],
                    ],
                ],
            ],
        ]);

        return $response->json('data');
    }

    /**
     * Set availability — make the IAP available in all territories.
     */
    public function createAvailability(string $iapId): array
    {
        $response = $this->http()->post(self::BASE_URL.'/v1/inAppPurchaseAvailabilities', [
            'data' => [
                'type' => 'inAppPurchaseAvailabilities',
                'attributes' => [
                    'availableInNewTerritories' => true,
                ],
                'relationships' => [
                    'inAppPurchase' => [
                        'data' => ['type' => 'inAppPurchases', 'id' => $iapId],
                    ],
                    'availableTerritories' => [
                        // Empty means "all territories" when combined with
                        // availableInNewTerritories: true
                    ],
                ],
            ],
        ]);

        return $response->json('data');
    }

    /**
     * Generate a JWT for App Store Connect API authentication.
     * Token is valid for 20 minutes.
     */
    private function generateJwt(): string
    {
        $header = [
            'alg' => 'ES256',
            'kid' => $this->keyId,
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $this->issuerId,
            'iat' => $now,
            'exp' => $now + 1200, // 20 minutes
            'aud' => 'appstoreconnect-v1',
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signingInput = "{$headerEncoded}.{$payloadEncoded}";

        $privateKey = $this->privateKey;
        if (! str_contains($privateKey, '-----BEGIN')) {
            $privateKey = base64_decode($privateKey);
        }

        $pkey = openssl_pkey_get_private($privateKey);
        if ($pkey === false) {
            throw new RuntimeException('Failed to parse App Store Connect private key: '.openssl_error_string());
        }

        openssl_sign($signingInput, $signature, $pkey, OPENSSL_ALGO_SHA256);

        // Convert DER to raw R||S format for ES256
        $signature = $this->derToRaw($signature);

        return $signingInput.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Convert DER-encoded ECDSA signature to raw R||S format (64 bytes).
     */
    private function derToRaw(string $der): string
    {
        $offset = 2;
        $rLen = ord($der[$offset + 1]);
        $r = substr($der, $offset + 2, $rLen);
        $offset += 2 + $rLen;
        $sLen = ord($der[$offset + 1]);
        $s = substr($der, $offset + 2, $sLen);

        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r.$s;
    }
}
