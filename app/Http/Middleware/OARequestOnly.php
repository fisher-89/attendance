<?php

namespace App\Http\Middleware;

use Closure;

class OARequestOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->getClientIp(), ['120.77.14.132'])) {
            return $next($request);
        } else {
            return response('无访问权限', 403);
        }
    }
}
