<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $roleName = strtolower($user->role->role_name);

        $allowedRoles = array_map('strtolower', $roles);

        if (!in_array($roleName, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak!'
            ], 403);
        }

        return $next($request);
    }
}