<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Memerlukan hak administrator.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akses ditolak. Halaman ini memerlukan hak akses administrator.',
            ]);
        }

        return $next($request);
    }
}
