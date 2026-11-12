<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableAdminActivityLogging
{
    private const MUTATING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        activity()->disableLogging();

        $shouldLog = in_array($request->method(), self::MUTATING, true)
            && $request->routeIs(['superadmin.*', 'lembaga.*', 'admin.*']);

        if ($shouldLog) {
            activity()->enableLogging();
        }

        try {
            return $next($request);
        } finally {
            activity()->enableLogging();
        }
    }
}
