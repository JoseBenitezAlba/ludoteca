<?php

namespace App\Http\Middleware;

use App\Services\OpenCritic;
use Closure;
use Illuminate\Http\Request;

/** Cuando el monitor de disponibilidad visita la web, aprovecha para rellenar una nota de crítica. */
class RellenarNotas
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (str_contains((string) $request->userAgent(), 'UptimeRobot')) {
            app(OpenCritic::class)->rellenarUno();
        }
    }
}
