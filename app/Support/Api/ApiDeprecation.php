<?php

namespace App\Support\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiDeprecation
{
    public static function apply(Response $response, Request $request): Response
    {
        if (! self::applies($request)) {
            return $response;
        }

        foreach (self::headers() as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    public static function applies(Request $request): bool
    {
        if (! (bool) config('api.legacy.enabled', true)) {
            return false;
        }

        foreach ((array) config('api.legacy.paths', []) as $pattern) {
            if (is_string($pattern) && $pattern !== '' && $request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function headers(): array
    {
        $headers = [
            'Deprecation' => 'true',
        ];

        $sunset = trim((string) config('api.legacy.sunset', ''));

        if ($sunset !== '') {
            try {
                $headers['Sunset'] = Carbon::parse($sunset)->utc()->toRfc7231String();
            } catch (\Throwable) {
                // Konfigurasi tanggal tidak valid tidak boleh membuat API gagal.
            }
        }

        $documentation = trim((string) config('api.legacy.documentation_url', ''));

        if ($documentation !== '') {
            $headers['Link'] = '<'.$documentation.'>; rel="deprecation"';
        }

        return $headers;
    }
}
