<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\HolidayRepositories;
use Illuminate\Support\Facades\DB;
class HolidayController extends Controller
{
    //    
    public $holidayRepos;
	public function __construct(HolidayRepositories $holidayRepos){
		$this->holidayRepos = $holidayRepos;
	}

	public function list(Request $request){
		#header("Access-Control-Allow-Origin:*");
		#header("Content-Type: application/json");
		$callback =  $request->callback;
		// return $callback.'({'."sb:sd".'})';
		return $callback.'('.json_encode($this->holidayRepos->getlist($request)).')';
	}

	public function cancel(Request $request){
		header("Access-Control-Allow-Origin:*");
		$id = $request->id;
		return $this->holidayRepos->cancel($id);
	}

	public function imports(Request $request){
	// return base_path('public/');
#	return $_FILES;
#return $_FILES['file']['tmp_name'];
	$files =  $request->input('file');
	$saveData = json_decode($files,true);
#	$saveData = ['staff_sn'=>1888];
 	$res = DB::table('holiday')->insert($saveData);
		return ['status'=>1,'msg'=>"ok",'data'=>$res];
	}
}
