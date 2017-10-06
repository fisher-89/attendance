<?php

namespace App\Http\Middleware;

use Closure;

class Initial
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
        if (!app('CurrentUser')->isLogin()) {
            app('CurrentUser')->login();
        }
        return $next($request);
    }

}
