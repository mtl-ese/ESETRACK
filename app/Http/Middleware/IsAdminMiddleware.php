<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is logged in and is an admin
        if (auth()->check() && auth()->user()->isAdmin == 1 || auth()->check() && auth()->user()->isSuperAdmin == 1) {
            return $next($request);
        }

        // If the user is not an admin, redirect to a different page (e.g., home)
        return redirect()
            ->route('dashboard')
            ->with('error', 'You do not have admin access.');
    }
}
