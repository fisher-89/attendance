<?php

namespace App\Http\Middleware;

use Closure;

class Ser
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
        // session(['ser'=>'liuliu']);
        $ser = session('ser');
        if($ser){
            echo 'aaa';
        }else{
            echo 'bbb';
        }

        // echo session('ser');
        // echo 111;
        return $next($request);
    }
}
