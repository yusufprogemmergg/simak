<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsOwner
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk owner'
            ], 403);
        }

        return $next($request);
    }
}