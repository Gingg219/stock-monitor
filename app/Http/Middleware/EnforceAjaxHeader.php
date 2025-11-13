<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceAjaxHeader
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*') && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return response()->json(['message' => 'Missing X-Requested-With header'], 400);
        }
        return $next($request);
    }
}
