<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Models\ApiIdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiIdempotency
{
    private const LOCK_SECONDS = 30;

    private const TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        $key = trim((string) $request->header('Idempotency-Key', ''));

        if ($key === '') {
            throw ApiException::make(
                ApiErrorCode::VALIDASI_GAGAL,
                fields: ['idempotency_key' => ['Header Idempotency-Key wajib dikirim.']],
            );
        }

        if (mb_strlen($key) > 100) {
            throw ApiException::make(
                ApiErrorCode::VALIDASI_GAGAL,
                fields: ['idempotency_key' => ['Header Idempotency-Key maksimal 100 karakter.']],
            );
        }

        $user = $request->user();

        if (! $user) {
            throw ApiException::make(ApiErrorCode::SESI_BERAKHIR);
        }

        $userId = (int) $user->id;
        $method = $request->method();
        $path = trim($request->path(), '/');
        $hash = $this->payloadHash($request);

        [$record, $replay] = $this->claim($userId, $key, $method, $path, $hash);

        if ($replay !== null) {
            return $replay;
        }

        try {
            $response = $next($request);
        } catch (\Throwable $exception) {
            $record->delete();

            throw $exception;
        }

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $record->update([
                'status' => 'completed',
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
                'response_headers' => array_filter([
                    'Content-Type' => $response->headers->get('Content-Type'),
                    'X-Request-ID' => $response->headers->get('X-Request-ID'),
                ]),
                'locked_until' => null,
            ]);
        } else {
            $record->delete();
        }

        return $response;
    }

    /**
     * @return array{0: ApiIdempotencyKey, 1: Response|null}
     */
    private function claim(
        int $userId,
        string $key,
        string $method,
        string $path,
        string $hash,
    ): array {
        $record = $this->findActive($userId, $key, $method, $path);

        if ($record !== null) {
            return $this->resume($record, $hash);
        }

        try {
            $record = ApiIdempotencyKey::create([
                'user_id' => $userId,
                'key' => $key,
                'method' => $method,
                'path' => $path,
                'request_hash' => $hash,
                'status' => 'processing',
                'locked_until' => now()->addSeconds(self::LOCK_SECONDS),
                'expires_at' => now()->addHours(self::TTL_HOURS),
            ]);

            return [$record, null];
        } catch (QueryException $exception) {
            // Request paralel dengan key yang sama bisa menang lebih dulu.
            $record = $this->findActive($userId, $key, $method, $path);

            if ($record === null) {
                throw $exception;
            }

            return $this->resume($record, $hash);
        }
    }

    /**
     * @return array{0: ApiIdempotencyKey, 1: Response|null}
     */
    private function resume(ApiIdempotencyKey $record, string $hash): array
    {
        if (! hash_equals($record->request_hash, $hash)) {
            throw ApiException::make(ApiErrorCode::IDEMPOTENCY_KEY_BERBEDA);
        }

        if ($record->status === 'completed') {
            return [$record, $this->replay($record)];
        }

        if ($record->locked_until !== null && $record->locked_until->isFuture()) {
            throw ApiException::make(ApiErrorCode::PERMINTAAN_SEDANG_DIPROSES);
        }

        $record->update([
            'status' => 'processing',
            'request_hash' => $hash,
            'response_status' => null,
            'response_body' => null,
            'response_headers' => null,
            'locked_until' => now()->addSeconds(self::LOCK_SECONDS),
            'expires_at' => now()->addHours(self::TTL_HOURS),
        ]);

        return [$record->fresh(), null];
    }

    private function findActive(int $userId, string $key, string $method, string $path): ?ApiIdempotencyKey
    {
        ApiIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->where('method', $method)
            ->where('path', $path)
            ->where('expires_at', '<=', now())
            ->delete();

        return ApiIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->where('method', $method)
            ->where('path', $path)
            ->where('expires_at', '>', now())
            ->first();
    }

    private function replay(ApiIdempotencyKey $record): Response
    {
        return response(
            $record->response_body ?? '',
            $record->response_status ?? 200,
            $record->response_headers ?? [],
        );
    }

    private function payloadHash(Request $request): string
    {
        $files = [];
        $fileKeys = array_keys($request->allFiles());

        foreach ($request->allFiles() as $key => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $realPath = $file->getRealPath();

            $files[$key] = [
                'name' => $file->getClientOriginalName(),
                'size' => (int) $file->getSize(),
                'hash' => $realPath !== false ? hash_file('sha256', $realPath) : null,
            ];
        }

        $payload = Arr::sortRecursive($request->except($fileKeys));

        return hash('sha256', json_encode([
            'payload' => $payload,
            'files' => $files,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }
}
