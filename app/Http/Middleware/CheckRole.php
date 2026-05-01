<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce that the authenticated user has one of the given Spatie roles.
 *
 * Usage in routes:
 *   Route::middleware('role:admin')
 *   Route::middleware('role:admin,teacher')
 */
final class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Required role: '.implode(' or ', $roles).'.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
