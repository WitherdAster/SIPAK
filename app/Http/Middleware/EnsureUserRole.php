<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * Penggunaan: Route::middleware(['auth', 'role:admin'])->...
     * atau: Route::middleware(['auth', 'role:admin,operator'])->...
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! empty($roles) && ! in_array($user->role, $roles, true))) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
