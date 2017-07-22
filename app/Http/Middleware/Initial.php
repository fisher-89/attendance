<?php

namespace App\Http\Middleware;

use Closure;

class Initial {

    private $url;
    private $redirectUrl;
    public $OASer;

    public function __construct() {
        $this->url = 'get_current_user';
        $this->OASer = new \App\Services\OAService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $token = $request->user_token;
        $username = session('username');
        if (!$username) {
            $userInfo = $this->OASer->getDataFromApi($this->url);
            if ($userInfo['status'] == 1) {
                session(['username' => $userInfo['message']['realname']]);
                session(['staff_sn' => $userInfo['message']['staff_sn']]);
            }
        }
        // return redirect('/loginError')->with(['redirectUrl'=>$this->redirectUrl]);
        # $userInfo = $this->OASer->getDataFromApi($this->url);
        // dd($userInfo);
        // dd(session()->all());
        // dd(cache('OA_appToken_112068'));
        // echo json_encode(session()->all());
// dd(session('staff_sn'));
        return $next($request);
    }

}
