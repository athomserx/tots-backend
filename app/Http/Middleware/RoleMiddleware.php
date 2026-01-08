<?php

namespace App\Http\Middleware;

use App\Enums\RoleType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $allowedRoleIds = collect($roles)->map(function ($role) {
            if (is_numeric($role)) {
                return (int) $role;
            }

            return match (strtolower($role)) {
                'client' => RoleType::CLIENT->value,
                'admin' => RoleType::ADMIN->value,
                default => null,
            };
        })->filter()->toArray();

        if (!in_array($user->role_id, $allowedRoleIds)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource',
            ], 403);
        }

        return $next($request);
    }
}
