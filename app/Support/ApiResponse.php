<?php

namespace App\Support;

use App\Exceptions\ApiErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, string|null>  $links
     */
    public static function success(
        mixed $data,
        array $meta = [],
        ?string $message = null,
        int $status = 200,
        array $links = [],
    ): JsonResponse {
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        if ($links !== []) {
            $payload['links'] = $links;
        }

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status, [
            'X-Request-ID' => self::requestId(),
        ]);
    }

    /**
     * @param  array<string, array<int, string>>  $fields
     * @param  array<string, string>  $headers
     */
    public static function error(
        string $code,
        ?string $message = null,
        array $fields = [],
        array $headers = [],
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message ?? ApiErrorCode::message($code),
        ];

        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        $error['request_id'] = self::requestId();

        return response()->json(
            ['error' => $error],
            ApiErrorCode::status($code),
            array_merge([
                'X-Request-ID' => self::requestId(),
            ], $headers),
        );
    }

    public static function requestId(): string
    {
        $request = request();
        $requestId = $request->attributes->get('request_id');

        if (! is_string($requestId) || $requestId === '') {
            $requestId = 'req_'.(string) Str::ulid();
            $request->attributes->set('request_id', $requestId);
        }

        return $requestId;
    }
}
