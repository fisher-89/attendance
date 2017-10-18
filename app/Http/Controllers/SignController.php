<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignController extends Controller
{

    public $signRepos;

    public function __construct()
    {
        $this->signRepos = app('ClockRepos');
    }

    public function getRecord(Request $request)
    {
        $date = $request->get('date');
        $staffSn = $request->get('staff_sn');
        return $this->signRepos->getRecord($date,$staffSn);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'type' => ['required'],
            'lng' => ['required', 'numeric'],
            'lat' => ['required', 'numeric'],
            'address' => ['max:200'],
        ]);
        return $this->signRepos->sign($request);

    }

}
