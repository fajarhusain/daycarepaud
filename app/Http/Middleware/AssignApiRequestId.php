<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignApiRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->resolveRequestId($request);
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    private function resolveRequestId(Request $request): string
    {
        $candidate = trim((string) $request->header('X-Request-ID', ''));

        if ($candidate !== '' && preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $candidate) === 1) {
            return $candidate;
        }

        return 'req_'.(string) Str::ulid();
    }
}
