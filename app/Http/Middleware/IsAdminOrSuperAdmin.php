<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminOrSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Unauthorized access');
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            return redirect('/login')->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
}

