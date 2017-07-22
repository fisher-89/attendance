<?php 
namespace App\Repositories;

use App\Models\Transfer;

class TransferRepositories{
	
	public function goShop($staff_sn){
		if(!$staff_sn) return ['status'=>0,'msg'=>config('hints.102')];
		$data = Transfer::where([['status',0],['staff_sn',$staff_sn]])->get(['id']);
		return $data[0]['id']?['status'=>1,'msg'=>config('hints.101'),'data'=>$data[0]]:['status'=>0,'msg'=>config('hints.100')];
		return 'goShopRepositorires';
	}


	/**
    *  根据店铺名 获取调动员工信息 
    *  @param    shopsn
    */
	public function getTransferShop($input=[]){ 
		// if(!isset($input['shopsn'])) return returnErr('hints.102');
		// $curTime = time();
		// $data = Transfer::get();
		// $input['shopsn'] = 'go0020';
		$input['shopsn'] = session('shopsn');
		$data = Transfer::where('out_shop_sn',$input['shopsn'])->orWhere('go_shop_sn',$input['shopsn'])->orderBy('created_at','desc')->get();
		// return $data;
		// return $input;
		return returnRes($data,'hints.101','hints.102');
		 
		$data = Transfer::where([['out_shop_sn',$input['shopsn']],['go_shop_sn',$input['shopsn']]])->get();
	}



	/**
	* 查询 员工是否有调动状态
	*/
	public function transferRecord($staff_sn){
		if(!$staff_sn) return ['status'=>0,'msg'=>config('hints.102')];
		// $data = Transfer::where([['status','<>',3],['status','<>',2],['staff_sn',$staff_sn]])->get(['id']);
		$data = Transfer::where([['status','<',2],['staff_sn',$staff_sn]])->get(['id']);
		return isset($data[0])?['status'=>1,'msg'=>config('hints.101'),'data'=>$data[0]]:['status'=>0,'msg'=>config('hints.110')];
	}

	/**
	* 更新
	* params      id
	* start_time  time
	*/
	public function updata($input){
		$upArr = [];
		$acceptArr = [
			'out_shop_time'=>'out_shop_time',
			'go_shop_time'=>'go_shop_time',
		];
		// return $input;
		if(!isset($input['id'])) return returnErr('hints.102');
		if(!isset($acceptArr[$input['type']])) return returnErr('hints.130');
		// return $input;
		$transferInfo = Transfer::find($input['id']);
		// if($transferInfo)
		// return $transferInfo;
		if($input['type'] == 'go_shop_time'){
			if(empty($transferInfo['out_shop_time'])) return returnErr('hints.161');
		}

		if(!isset($transferInfo['id'])) return returnErr('hints.100');


		$transferInfo[$input['type']] = date('Y-m-d H:i:s',time());

		$res = $transferInfo->save();
		// return $res;
		return $res?['status'=>1,'msg'=>config('hints.160')]:['status'=>0];	
	}


	/**
	* 编辑
	* params      id
	* start_time  time
	*/

	public function edit($input){
		if(!isset($input['id'])) {return ['status' => 0, 'msg' => config('hints.108')]; };
		$hintsArr = [
			'调动完成'=>config('hints.121'),
			'调动取消'=>config('hints.122'),
			];
		$transferRes = Transfer::where('id',$input['id'])->select('status')->get()->toArray();
		if(isset($hintsArr[$transferRes[0]['status']])){
			return ['status'=>0,'msg'=>$hintsArr[$transferRes[0]['status']]]; 
		}
		if(isset($input['budget'])){
			$day = 86399;
			$input['budget'] = strtotime($input['budget'])+$day;
		}
		// return $input;
		$res = Transfer::where('id',$input['id'])->update($input);
		#return $res?['status'=>1,'msg'=>config('hints.107')]:['status'=>0,'msg'=>config('hints.108')];
	}

	/**
    *	添加调动单
    */
	public function  save($input){
		//查询最近7天调动记录
		$history = date('Y-m-d H:i:s',time()-604880);
		$day = 86399;
		if(isset($input['budget'])){
			$input['budget'] = strtotime($input['budget'])+$day;
		}
		$historyRes = Transfer::where('staff_sn',$input['staff_sn'])->where('status','<',2)->where('created_at','>',$history)->get()->toArray();
		if($historyRes){
			return ['status'=>0,'msg'=>config('hints.120')];
		}
        $res = Transfer::create($input);
        return $res?['status'=>1,'msg'=>config('hints.105')]:['status'=>0,'msg'=>config('hints.106')];
    }

    /**
    *	添加调动时间
    */
	// public function  saveRecord($input){6
	// 	$saveArr['parentid'] = $input['tid'];
 //        return Transfer::create($input);
 //    }

    /**
    *	调动记录
    */
    public function recordList($input){
    	$take = $input['take']??15;
    	$skip = isset($input['skip'])?$input['skip']*$take:0;
    	return Transfer::where('staff_sn',$input['staff_sn'])->skip($skip)->take($take)->orderBy('created_at','desc')->get();
    }

    /**
    *	删除
    */
    public function del($id){
    	if(!$id) return returnErr('hints.102');
    	$res = Transfer::destroy($id);
    	return returnRes($res,'hints.123','hints.124');
    	
    }
}