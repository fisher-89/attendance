<?php

namespace App\Http\Middleware;

use Closure;

class Initial
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token   = $request->user_token;
        $staffSn = session('staff_sn');
        $staffSn = false;
        if (!$staffSn) {
            app('CurrentUser')->login();
        }
        return $next($request);
    }

}
