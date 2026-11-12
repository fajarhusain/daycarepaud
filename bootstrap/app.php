<?php

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Middleware\AssignApiRequestId;
use App\Http\Middleware\EnableAdminActivityLogging;
use App\Http\Middleware\EnsureAdminMenuAccess;
use App\Http\Middleware\EnsureApiIdempotency;
use App\Http\Middleware\EnsureApiRole;
use App\Http\Middleware\EnsureApiTenant;
use App\Http\Middleware\EnsureLembagaSekolahContext;
use App\Http\Middleware\EnsureSekolahActive;
use App\Http\Middleware\SetApiLocale;
use App\Http\Middleware\SetTenantContext;
use App\Support\Api\ApiDeprecation;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('monev:generate')->monthlyOn(1, '02:00')
            ->onFailure(function () {
                Log::error('Scheduled command monev:generate FAILED');
            });
        $schedule->command('monev:finalize-stale')->hourly()
            ->onFailure(function () {
                Log::error('Scheduled command monev:finalize-stale FAILED');
            });
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'admin.menu' => EnsureAdminMenuAccess::class,
            'lembaga.sekolah' => EnsureLembagaSekolahContext::class,
            'tenant.context' => SetTenantContext::class,
            'sekolah.active' => EnsureSekolahActive::class,
            'admin.activity' => EnableAdminActivityLogging::class,
            'api.request-id' => AssignApiRequestId::class,
            'api.locale' => SetApiLocale::class,
            'api.role' => EnsureApiRole::class,
            'api.tenant' => EnsureApiTenant::class,
            'api.idempotency' => EnsureApiIdempotency::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ApiException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $exception->toResponse($request);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiException::make(
                ApiErrorCode::VALIDASI_GAGAL,
                fields: $exception->errors(),
            )->toResponse($request);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiException::make(ApiErrorCode::SESI_BERAKHIR)->toResponse($request);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN)->toResponse($request);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiException::make(ApiErrorCode::AKSES_DITOLAK)->toResponse($request);
        });

        $exceptions->render(function (SpatieUnauthorizedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiException::make(ApiErrorCode::AKSES_DITOLAK)->toResponse($request);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $code = match ($exception->getStatusCode()) {
                400 => ApiErrorCode::PERMINTAAN_TIDAK_VALID,
                401 => ApiErrorCode::SESI_BERAKHIR,
                403 => ApiErrorCode::AKSES_DITOLAK,
                404 => ApiErrorCode::DATA_TIDAK_DITEMUKAN,
                405 => ApiErrorCode::METODE_TIDAK_DIDUKUNG,
                409 => ApiErrorCode::KONFLIK_DATA,
                410 => ApiErrorCode::TAUTAN_KEDALUWARSA,
                413 => ApiErrorCode::FILE_TERLALU_BESAR,
                415 => ApiErrorCode::FORMAT_FILE_TIDAK_DIDUKUNG,
                422 => ApiErrorCode::VALIDASI_GAGAL,
                429 => ApiErrorCode::TERLALU_BANYAK_PERMINTAAN,
                503 => ApiErrorCode::LAYANAN_TIDAK_TERSEDIA,
                default => null,
            };

            if ($code === null) {
                return null;
            }

            $response = ApiException::make($code)->toResponse($request);

            foreach ($exception->getHeaders() as $name => $value) {
                $response->headers->set($name, $value);
            }

            return $response;
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            // Jangan catat pesan exception mentah: pesan QueryException dan
            // exception domain lain bisa memuat token, NIK, email, atau data pribadi.
            Log::error('Unhandled API exception.', [
                'request_id' => ApiResponse::requestId(),
                'exception' => $exception::class,
                'method' => $request->method(),
                'path' => $request->path(),
            ]);

            return ApiException::make(ApiErrorCode::KESALAHAN_SERVER)->toResponse($request);
        });

        $exceptions->respond(function ($response, $e) {
            if ($response->getStatusCode() === 419 && ! request()->is('api/*')) {
                return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir, silakan login kembali.');
            }

            return ApiDeprecation::apply($response, request());
        });
    })->create();
