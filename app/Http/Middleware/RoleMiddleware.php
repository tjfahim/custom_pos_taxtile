<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // DEBUG: Log user info and requested roles
        Log::info('RoleMiddleware check', [
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames()->toArray(),
            'requested_roles' => $role,
        ]);
        
        // Split by comma to handle multiple roles
        $allowedRoles = explode(',', $role);
        $allowedRoles = array_map('trim', $allowedRoles);
        
        // Check if user has any of the allowed roles
        foreach ($allowedRoles as $allowedRole) {
            Log::info('Checking role', ['role' => $allowedRole, 'hasRole' => $user->hasRole($allowedRole)]);
            if ($user->hasRole($allowedRole)) {
                Log::info('Access granted for role', ['role' => $allowedRole]);
                return $next($request);
            }
        }
        
        Log::warning('Access denied', [
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames()->toArray(),
            'required_roles' => $allowedRoles
        ]);
        
        return abort(403, 'You do not have permission to access this page.');
    }
}