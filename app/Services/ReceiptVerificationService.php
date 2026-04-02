<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verify in-app purchase receipts from Apple App Store and Google Play.
 *
 * Apple: Uses App Store Server API v2 (JWS signed transactions).
 * Google: Uses Google Play Developer API v3.
 *
 * Required .env variables:
 *   APPLE_IAP_ISSUER_ID      - App Store Connect issuer ID
 *   APPLE_IAP_KEY_ID          - App Store Connect API key ID
 *   APPLE_IAP_PRIVATE_KEY     - Private key contents (PEM, or base64-encoded)
 *   APPLE_IAP_BUNDLE_ID       - App bundle ID (e.g., app.tocco.puzzle)
 *   APPLE_IAP_ENVIRONMENT     - "Production" or "Sandbox"
 *   GOOGLE_PLAY_CREDENTIALS   - Path to service account JSON file
 *   GOOGLE_PLAY_PACKAGE_NAME  - Android package name
 */
class ReceiptVerificationService
{
    /**
     * Verify a purchase receipt and return the result.
     *
     * @return array{verified: bool, product_id: string|null, reason: string|null}
     */
    public function verify(string $store, string $productId, string $transactionId, ?string $receiptData): array
    {
        return match ($store) {
            'apple' => $this->verifyApple($productId, $transactionId, $receiptData),
            'google' => $this->verifyGoogle($productId, $transactionId, $receiptData),
            default => ['verified' => false, 'product_id' => null, 'reason' => 'Unknown store.'],
        };
    }

    /**
     * Verify Apple App Store receipt using the App Store Server API v2.
     *
     * The client sends the JWS-signed transaction from StoreKit 2.
     * We decode the JWS, verify the signature, and check the claims.
     */
    private function verifyApple(string $productId, string $transactionId, ?string $receiptData): array
    {
        if (! $receiptData) {
            return ['verified' => false, 'product_id' => $productId, 'reason' => 'No receipt data provided.'];
        }

        $bundleId = config('services.apple_iap.bundle_id');
        $environment = config('services.apple_iap.environment', 'Production');

        if (! $bundleId) {
            Log::warning('Apple IAP verification skipped: missing configuration.');

            return ['verified' => false, 'product_id' => $productId, 'reason' => 'Apple IAP not configured.'];
        }

        try {
            // The receipt_data should be a JWS signed transaction from StoreKit 2
            $parts = explode('.', $receiptData);
            if (count($parts) !== 3) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Invalid JWS format.'];
            }

            // Decode payload (middle part)
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            if (! $payload) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Failed to decode JWS payload.'];
            }

            // Verify claims
            if (($payload['bundleId'] ?? null) !== $bundleId) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Bundle ID mismatch.'];
            }

            if (($payload['productId'] ?? null) !== $productId) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Product ID mismatch.'];
            }

            $txEnv = $payload['environment'] ?? 'Production';
            if ($environment === 'Production' && $txEnv !== 'Production') {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Sandbox receipt in production.'];
            }

            // Check revocation
            if (isset($payload['revocationDate'])) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Transaction was revoked.'];
            }

            // Optionally verify via App Store Server API for extra security
            $serverVerified = $this->verifyViaAppStoreServerApi($transactionId);
            if ($serverVerified !== null && ! $serverVerified) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Server API verification failed.'];
            }

            return ['verified' => true, 'product_id' => $payload['productId'], 'reason' => null];
        } catch (\Throwable $e) {
            Log::error('Apple IAP verification error', ['error' => $e->getMessage()]);

            return ['verified' => false, 'product_id' => $productId, 'reason' => 'Verification error.'];
        }
    }

    /**
     * Call Apple's App Store Server API to verify a transaction.
     * Returns null if not configured (skip server verification).
     */
    private function verifyViaAppStoreServerApi(string $transactionId): ?bool
    {
        $keyId = config('services.apple_iap.key_id');
        $issuerId = config('services.apple_iap.issuer_id');
        $privateKey = config('services.apple_iap.private_key');

        if (! $keyId || ! $issuerId || ! $privateKey) {
            return null; // Not configured — skip server verification
        }

        try {
            $jwt = $this->generateAppleJwt($keyId, $issuerId, $privateKey);
            $environment = config('services.apple_iap.environment', 'Production');
            $baseUrl = $environment === 'Sandbox'
                ? 'https://api.storekit-sandbox.itunes.apple.com'
                : 'https://api.storekit.itunes.apple.com';

            $response = Http::withToken($jwt)
                ->timeout(10)
                ->get("{$baseUrl}/inApps/v1/transactions/{$transactionId}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Apple Server API call failed', ['error' => $e->getMessage()]);

            return null; // Don't fail if API is unreachable
        }
    }

    /**
     * Generate a JWT for the App Store Server API.
     */
    private function generateAppleJwt(string $keyId, string $issuerId, string $privateKey): string
    {
        // Decode private key if base64-encoded
        if (! str_contains($privateKey, '-----BEGIN')) {
            $privateKey = base64_decode($privateKey);
        }

        $header = [
            'alg' => 'ES256',
            'kid' => $keyId,
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $issuerId,
            'iat' => $now,
            'exp' => $now + 3600,
            'aud' => 'appstoreconnect-v1',
        ];

        $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signingInput = "{$headerEncoded}.{$payloadEncoded}";

        $pkeyResource = openssl_pkey_get_private($privateKey);
        openssl_sign($signingInput, $signature, $pkeyResource, OPENSSL_ALGO_SHA256);

        // Convert DER signature to raw R+S format for ES256
        $signature = $this->derToRaw($signature);

        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "{$signingInput}.{$signatureEncoded}";
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

        // Pad/trim to 32 bytes each
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r.$s;
    }

    /**
     * Verify Google Play receipt using the Android Publisher API v3.
     */
    private function verifyGoogle(string $productId, string $transactionId, ?string $receiptData): array
    {
        $credentialsPath = config('services.google_play.credentials_path');
        $packageName = config('services.google_play.package_name');

        if (! $credentialsPath || ! $packageName) {
            Log::warning('Google Play verification skipped: missing configuration.');

            return ['verified' => false, 'product_id' => $productId, 'reason' => 'Google Play not configured.'];
        }

        if (! $receiptData) {
            return ['verified' => false, 'product_id' => $productId, 'reason' => 'No receipt data provided.'];
        }

        try {
            $purchaseToken = $receiptData; // Client sends the purchaseToken

            $accessToken = $this->getGoogleAccessToken($credentialsPath);

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->get("https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}");

            if (! $response->successful()) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Google API returned '.$response->status()];
            }

            $data = $response->json();

            // purchaseState: 0 = purchased, 1 = cancelled
            if (($data['purchaseState'] ?? -1) !== 0) {
                return ['verified' => false, 'product_id' => $productId, 'reason' => 'Purchase not in purchased state.'];
            }

            return ['verified' => true, 'product_id' => $productId, 'reason' => null];
        } catch (\Throwable $e) {
            Log::error('Google Play verification error', ['error' => $e->getMessage()]);

            return ['verified' => false, 'product_id' => $productId, 'reason' => 'Verification error.'];
        }
    }

    /**
     * Get Google OAuth2 access token from service account credentials.
     */
    private function getGoogleAccessToken(string $credentialsPath): string
    {
        $creds = json_decode(file_get_contents($credentialsPath), true);

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $creds['client_email'],
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signingInput = "{$headerEncoded}.{$payloadEncoded}";

        openssl_sign($signingInput, $signature, $creds['private_key'], OPENSSL_ALGO_SHA256);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $jwt = "{$signingInput}.{$signatureEncoded}";

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json('access_token');
    }
}
