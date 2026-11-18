<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class RoleMiddleware
{
        public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke resource ini.',
                ], 403);
            }

            return response('Unauthorized.', 403);
        }

        return $next($request);
    }
}