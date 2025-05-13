<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
  public function handle($request, Closure $next, $role)
{
   if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }

        // If the user does not have the required role, return an unauthorized response
        return response()->json(['message' => 'Unauthorized'], 403);
}

}