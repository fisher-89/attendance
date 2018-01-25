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
        if ($this->hasAppToken()) {
            //
        } elseif (session()->has('OA_refresh_token')) {
            $this->refreshAppToken();
        } elseif (request()->has('auth_code')) {
            $this->getAppToken();
        } else {
            if (empty($this->receiptUrl)) {
                $this->receiptUrl = url()->current();
            }
            $this->getAuthCode();
        }
        if (!app('CurrentUser')->isLogin()) {
            app('CurrentUser')->login();
        }
        return $next($request);
    }

}
