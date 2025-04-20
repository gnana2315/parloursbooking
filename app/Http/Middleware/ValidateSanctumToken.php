<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ValidateSanctumToken
{    
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Additional custom validation if needed
        if ($accessToken->created_at->lt(now()->subDays(30))) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
