<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class RoleAuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        // 1. Resolve user via Sanctum bearer token or auth guard
        $user = $request->user();

        if (!$user) {
            $tokenString = $request->bearerToken();
            if ($tokenString) {
                $token = PersonalAccessToken::findToken($tokenString);
                if ($token) {
                    $user = $token->tokenable;
                    $request->setUserResolver(fn () => $user);
                }
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check if customer is attempting to access admin endpoints
        if ($user->role === 'Customer' && $request->is('api/admin*')) {
            return response()->json([
                'message' => 'Access denied: Customers are not permitted to access bakery administration.'
            ], 403);
        }

        // Check specific allowed roles if specified on route
        if (!empty($allowedRoles)) {
            // Super Admin always satisfies any administrative role requirement
            $isAuthorized = ($user->role === 'Super Admin');

            if (!$isAuthorized) {
                // Exact role match
                if (in_array($user->role, $allowedRoles)) {
                    $isAuthorized = true;
                }
                // Branch Manager / Branch Admin equivalence
                elseif (in_array('Branch Admin', $allowedRoles) && $user->role === 'Branch Manager') {
                    $isAuthorized = true;
                }
                elseif (in_array('Branch Manager', $allowedRoles) && $user->role === 'Branch Admin') {
                    $isAuthorized = true;
                }
                // Branch Staff includes specialized branch staff roles
                elseif (in_array('Branch Staff', $allowedRoles) && in_array($user->role, [
                    'Branch Staff',
                    'Branch Seller',
                    'Branch Seller / Cashier',
                    'Cashier',
                    'Servant',
                    'Delivery Staff',
                ])) {
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) {
                return response()->json([
                    'message' => "Access denied: This action requires one of the following roles: " . implode(', ', $allowedRoles) . ". Your role is: {$user->role}."
                ], 403);
            }
        }

        return $next($request);
    }
}
