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
        return $this->signRepos->getRecord($date);
    }

    public function save(Request $request)
    {
        dd(123);
        $this->validate($request, [
            'type'    => ['required'],
            'lng'     => ['required', 'numeric'],
            'lat'     => ['required', 'numeric'],
            'address' => ['max:200'],
        ]);
        $distance = app('Clock')->getDistanceToShop($request->lng, $request->lat);
        if ($distance > config('options.invalid_distance')) {
            return returnErr('hints.111');
        } else {
            return $this->signRepos->sign($request,$distance);
        }

    }

}
