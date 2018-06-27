<?php

namespace App\Http\Middleware;

use App\Services\OAService;
use Closure;

class Initial extends OAService
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
