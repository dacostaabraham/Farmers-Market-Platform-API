<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Usage in routes: ->middleware('role:admin,supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden. Required role(s): ' . implode(', ', $roles),
            ], 403);
        }

        if (!$request->user()->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        return $next($request);
    }
}
