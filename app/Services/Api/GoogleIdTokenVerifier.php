<?php

namespace App\Services\Api;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Support\Api\GoogleIdentity;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleIdTokenVerifier
{
    public function verify(string $idToken): GoogleIdentity
    {
        $clientIds = config('api.google.client_ids', []);

        if (! is_array($clientIds) || $clientIds === []) {
            Log::error('Google Sign-In dipanggil tanpa GOOGLE_CLIENT_IDS / GOOGLE_*_CLIENT_ID.');

            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('api.google.timeout_seconds', 8))
                ->get((string) config('api.google.tokeninfo_url'), [
                    'id_token' => $idToken,
                ]);
        } catch (ConnectionException $exception) {
            // Jangan catat message: URL tokeninfo dapat memuat id_token.
            Log::warning('Google tokeninfo tidak dapat dihubungi.');

            throw ApiException::make(
                ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID,
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        $audience = $payload['aud'] ?? null;
        $audiences = is_array($audience) ? $audience : [$audience];

        if (array_intersect($audiences, $clientIds) === []) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        $issuer = (string) ($payload['iss'] ?? '');
        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        if ((int) ($payload['exp'] ?? 0) <= time()) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        if (! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            throw ApiException::make(ApiErrorCode::GOOGLE_EMAIL_TIDAK_TERVERIFIKASI);
        }

        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $subject = trim((string) ($payload['sub'] ?? ''));

        if ($subject === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ApiException::make(ApiErrorCode::GOOGLE_TOKEN_TIDAK_VALID);
        }

        return new GoogleIdentity(
            subject: $subject,
            email: $email,
            name: trim((string) ($payload['name'] ?? '')),
        );
    }
}
