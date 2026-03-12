<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user || $user->role->name !== 'admin') {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Access denied. Admin rights required'
                ]
            ], 403);
        }
        
        return $next($request);
    }
}