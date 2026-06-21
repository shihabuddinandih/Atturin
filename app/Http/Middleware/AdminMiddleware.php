<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows users with role 'admin'. Redirects superadmin to their own dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $role = auth()->user()->role;

            if ($role === 'superadmin') {
                // Superadmin should not access admin pages; redirect to their own dashboard.
                return redirect('/superadmin/withdrawals');
            }

            if ($role === 'admin') {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak.');
    }
}
