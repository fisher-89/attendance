<?php 
namespace App\Repositories;
use App\Models\Transfer;
use App\Models\TransferTimeRecord;

class TransferTimeRecordRepositories{


	/**
    *	添加调动时间
    */
	public function save($input){
		if(!isset($input['parentid'])) return ['status'=>0,'msg'=>'102'];
		$acceptArr = [
			'start_time'=>'start_time',
			'end_time'=>'end_time',
		];
		$curTime = time();
		// $input['start_time'] = isset($input['start_time'])?($input['start_time'] = $time):null; 
		if(isset($acceptArr[$input['time']])){
			$input[$input['time']]  = $curTime;
		}else{
			return ['status'=>0,'msg'=>'102'];
		}



		$res = TransferTimeRecord::create($input);

		if($res['id'] > 0){
			// 
			$record = Transfer::find($input['parentid']);

			if($input['time'] == 'end_time'){
				$budget = $record->budget;
				// $cc = date('Y-m-d',$curTime);
				$abnormal = intval(($curTime-strtotime($budget))/86400);
				// $abnormal = $curTime-strtotime($budget);
				// return $abnormal;
				Transfer::where('id',$input['parentid'])->update(['status'=>$input['status'],'abnormal'=>$abnormal]);
			}else{
				Transfer::where('id',$input['parentid'])->update(['status'=>$input['status']]);
			}

			
		}

		// return ['status'=>0,'msg'=>config('hints.106');
		return $res?['status'=>1,'msg'=>config('hints.112')]:['status'=>0,'msg'=>config('hints.113')];
	}

}