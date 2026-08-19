<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json(['message' => 'Forbidden: Anda tidak memiliki akses ke resource ini'], 403);
        }

        return $next($request);
    }
}