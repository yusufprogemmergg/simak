<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Super admin tidak butuh lisensi
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        if (!$user || !$user->hasActiveLicense()) {
            return response()->json([
                'success' => false,
                'message' => 'Lisensi tidak aktif'
            ], 403);
        }

        return $next($request);
    }
}