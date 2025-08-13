<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    use ApiResponser;

    public function handle(Request $request, Closure $next, string $role)
    {
        if (! $request->user() || $request->user()->role !== $role) {
            return $this->error('Forbidden', 403);
        }
        return $next($request);
    }
}


