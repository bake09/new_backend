<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as BaseEnsureFrontendRequestsAreStateful;

class CheckRequestOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)
     */
    public function handle(Request $request, Closure $next)
    {
        // Beispiel: Überprüfen, ob die Anfrage von einer bestimmten Domain stammt
        if ($this->shouldApplyMiddleware($request)) {
            // Wende die "EnsureFrontendRequestsAreStateful" Middleware an
            return app(BaseEnsureFrontendRequestsAreStateful::class)->handle($request, $next);
        }

        // Ansonsten die Anfrage ohne die Middleware fortsetzen
        return $next($request);
    }

    protected function shouldApplyMiddleware(Request $request): bool
    {
        // Bedingung zur Entscheidung, ob die Middleware angewendet werden soll
        // Beispiel: Anfrage-Header überprüfen
        // return $request->header('Origin') === 'http://10.3.16.167:9000';
        return false;
    }
}
