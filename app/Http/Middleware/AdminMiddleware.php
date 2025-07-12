<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard(config('admin.guard'))->check()) {
            return redirect()->route(config('admin.routes.login'))
                           ->with('error', 'Please login first.');
        }

        if (Auth::guard(config('admin.guard'))->user()->status !== 'active') {
            Auth::guard(config('admin.guard'))->logout();
            return redirect()->route(config('admin.routes.login'))
                           ->with('error', 'Your account is inactive.');
        }

        return $next($request);
    }
}