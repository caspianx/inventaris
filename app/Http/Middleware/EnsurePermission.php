<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        foreach ($permissions as $permission) {
            if ($user->canAccess($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke fitur ini.');
    }
}
