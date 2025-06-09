<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsRecruiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       // Check if the user is authenticated and has the 'recruiter' role.
        if (Auth::check() && Auth::user()->isRecruiter()) {
            return $next($request);
        }

        // If not a recruiter, return an unauthorized response.
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: You must be a recruiter to access this resource.'
        ], 403); // 403 Forbidden
    }
}