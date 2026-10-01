<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // updateQuietly = rápido, sin disparar eventos ni tocar updated_at
            Auth::user()->updateQuietly(['last_seen' => now()]);
        }

        return $next($request);
    }
}