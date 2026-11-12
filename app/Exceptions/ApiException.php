<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ApiException extends RuntimeException
{
    /**
     * @param  array<string, array<int, string>>  $fields
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $errorCode,
        ?string $errorMessage = null,
        public readonly array $fields = [],
        public readonly array $headers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($errorMessage ?? ApiErrorCode::message($errorCode), 0, $previous);
    }

    /**
     * @param  array<string, array<int, string>>  $fields
     * @param  array<string, string>  $headers
     */
    public static function make(
        string $errorCode,
        ?string $message = null,
        array $fields = [],
        array $headers = [],
        ?\Throwable $previous = null,
    ): self {
        return new self($errorCode, $message, $fields, $headers, $previous);
    }

    public function status(): int
    {
        return ApiErrorCode::status($this->errorCode);
    }

    public function toResponse(Request $request): JsonResponse
    {
        return ApiResponse::error(
            code: $this->errorCode,
            message: $this->getMessage(),
            fields: $this->fields,
            headers: $this->headers,
        );
    }
}
