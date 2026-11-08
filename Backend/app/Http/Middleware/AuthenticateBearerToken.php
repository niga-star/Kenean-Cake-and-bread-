<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateBearerToken
{
    /**
     * Handle an incoming request and resolve Sanctum bearer token if present.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            $bearer = $request->bearerToken();
            if ($bearer) {
                $token = PersonalAccessToken::findToken($bearer);
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                    $user->withAccessToken($token);
                    $request->setUserResolver(fn () => $user);
                }
            }
        }

        return $next($request);
    }
}

