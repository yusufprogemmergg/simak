<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya super admin yang diizinkan.'
            ], 403);
        }

        return $next($request);
    }
}
