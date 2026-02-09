<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow access if user has Spatie admin/Staff role OR custom role = 1
        if ($user->hasAnyRole(['admin', 'Staff']) || $user->role == 1) {
            return $next($request);
        }
        
        return abort(403, 'You do not have permission to access this page.');
    }
}