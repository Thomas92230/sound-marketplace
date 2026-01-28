<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'global', int $maxAttempts = 60): Response
    {
        $identifier = $request->ip() . '|' . ($request->user()?->id ?? 'guest');
        
        if (RateLimiter::tooManyAttempts($key . ':' . $identifier, $maxAttempts)) {
            return response()->json([
                'error' => 'Trop de requêtes. Réessayez dans ' . RateLimiter::availableIn($key . ':' . $identifier) . ' secondes.'
            ], 429);
        }

        RateLimiter::hit($key . ':' . $identifier, 60);

        return $next($request);
    }
}