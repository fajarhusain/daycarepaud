<?php

namespace App\Support;

class AiProvider
{
    public const CUSTOM = 'custom';

    /**
     * @return array<string, array{label: string, base_url: ?string, default_model: ?string, hint: string}>
     */
    public static function all(): array
    {
        return config('ai_providers', []);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function label(string $provider): string
    {
        return self::all()[$provider]['label'] ?? $provider;
    }

    public static function defaultModel(string $provider): ?string
    {
        return self::all()[$provider]['default_model'] ?? null;
    }

    public static function hint(string $provider): string
    {
        return self::all()[$provider]['hint'] ?? '';
    }

    public static function resolveBaseUrl(string $provider, ?string $customBaseUrl): string
    {
        if ($provider === self::CUSTOM) {
            if (! filled($customBaseUrl)) {
                throw new \InvalidArgumentException('Base URL wajib diisi untuk provider custom.');
            }

            $normalized = rtrim($customBaseUrl, '/');
            self::assertSafeCustomBaseUrl($normalized);

            return $normalized;
        }

        $preset = self::all()[$provider]['base_url'] ?? null;

        if (! filled($preset)) {
            throw new \InvalidArgumentException("Provider tidak dikenal: {$provider}");
        }

        return rtrim($preset, '/');
    }

    public static function assertSafeCustomBaseUrl(string $url): void
    {
        $parsed = parse_url($url);

        if (! is_array($parsed) || empty($parsed['host'])) {
            throw new \InvalidArgumentException('Base URL tidak valid.');
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Base URL custom harus diawali http:// atau https://');
        }

        $host = strtolower($parsed['host']);

        if (in_array($host, ['localhost', '0.0.0.0'], true) || str_ends_with($host, '.localhost')) {
            throw new \InvalidArgumentException('Base URL tidak boleh mengarah ke localhost.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : (gethostbynamel($host) ?: []);

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \InvalidArgumentException('Base URL tidak boleh mengarah ke alamat jaringan privat atau internal.');
            }
        }
    }
}
