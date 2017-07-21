<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SignRepositories;

class SignController extends Controller
{
    //
    public $signRepos;

	public function __construct(SignRepositories $signRepos){
		$this->signRepos = $signRepos;
	}

    //OA 调用接口
    public function list(Request $request){
        header("Access-Control-Allow-Origin:*");
        // return $plugin->dataTables($request,new Transfer());
        return $this->signRepos->dataTables($request);
    }

    public function save(Request $request){

    	$input = $request->except(['_url']);
        $input['staff_sn'] = session('staff_sn');
        $input['staff_name'] = session('username');
    	// return $input;
		return $this->signRepos->save($input);
    }

    public function selects(Request $request){
        $input = $request->except(['_url']);
        $input['usersn'] = session('staff_sn');
        // return $input['usersn'];
        // return $input;
        if(!isset($input['usersn']) || !isset($input['days']) ) return returnErr('hints.102');
        $year = date('Y');
        $month = date('m');
        $days = ($input['days'] <10)?'0'.$input['days']:$input['days'];
        $input['time'] = strtotime($year.$month.$days);
        // $input['morningTime'] = date('Y-m-d H:i:s',$input['time']+43200);
        // $input['afternoonTime'] = date('Y-m-d H:i:s',$input['time']+86399);
        $input['endTime'] = date('Y-m-d H:i:s',$input['time']+86399);
        $input['time']  = date('Y-m-d H:i:s',$input['time']);
        // return $input;  43200
        // return date('Y-m-d',$selectTime);
        return $this->signRepos->selects($input);

    }
}
