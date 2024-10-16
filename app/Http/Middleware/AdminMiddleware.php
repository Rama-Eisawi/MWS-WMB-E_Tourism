<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    use ApiResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is logged in and is an admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);  // Allow access if the user is an admin
        }

        // Return a 403 Forbidden response if not an admin
        return $this->failed('Forbidden: Admins only.', 403);
    }
}
