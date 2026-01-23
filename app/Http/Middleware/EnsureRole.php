<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $role = $user->role ?? UserRole::User;

        if ($role instanceof UserRole) {
            $role = $role->value;
        }

        $role = (string) $role;

        if (! in_array($role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

