<?php 
namespace App\Repositories;
use App\Models\Sign;

class SignRepositories{

	public function save($input){

		$accept = [
			'sign_time'=>'sign_time',
			'down_time'=>'down_time'
		];

		if(!isset($accept[$input['type']])) return returnErr('hints.102');
 
		$input[$input['type']] = date('Y-m-d H:i:s',time());
		// return $input ;
		$res = Sign::create($input);
		// return $res;
		return returnRes($res['id'],'hints.112','hints.113');
	}


	public function selects($input){
		$accept = [''];
		$towork = [];  //上班
		$offwork = [];  //下班
		array_push($towork,['staff_sn',$input['usersn']]);
		array_push($offwork,['staff_sn',$input['usersn']]);
		if(isset($input['time'])){
			array_push($towork,['sign_time','>',$input['time']]);
			array_push($towork, ['sign_time','<',$input['endTime']]);

			array_push($offwork,['down_time','>',$input['time']]);
			array_push($offwork, ['down_time','<',$input['endTime']]);
		}

		$toworkRes = Sign::where($towork)->take(5)->orderBy('created_at','desc')->get();
		$offworkRes = Sign::where($offwork)->take(5)->orderBy('created_at','desc')->get();
		// return $offworkRes;
		return compact('toworkRes','offworkRes');
		return returnRes($res,'hints.101','hints.100');
	}

	//OA调用
	public function dataTables($request){
		return $plugin->dataTables($request,new Sign());
	}


}