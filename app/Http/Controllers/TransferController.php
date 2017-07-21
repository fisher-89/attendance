<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PluginService;
use App\Models\Transfer;
use App\Repositories\TransferRepositories;
use App\Repositories\TransferTimeRecordRepositories;
use App\Services\CurlService;


class TransferController extends Controller
{
	
	public $transferRepos;
	public $transferTimeRecordRepos;

    public function __construct(TransferRepositories $transferRepos , TransferTimeRecordRepositories $transferTimeRecordRepos){
    	$this->transferRepos = $transferRepos;
    	$this->transferTimeRecordRepos = $transferTimeRecordRepos;
    }

    public function dd(){
    	// $res = Transfer::find(178); 
        $arr = ['a','b','c'];

        dd( array_map(null,$arr));

        die();
    	 
    }

    //OA 调用接口
    public function list(Request $request ,PluginService $plugin){
        #header("Access-Control-Allow-Origin:*");
        $data = json_encode($plugin->dataTables($request,new Transfer()));
        return $request->callback.'('.$data.')';
        // return 'ssssssssss';
        #return $plugin->dataTables($request,new Transfer());
    }

    /**
    *  获取用户基本信息 和  调动信息
    */
    public function getuser(){
        
        if(session('staff_sn')){
            $data=['usersn'=>session('staff_sn'),'username'=>session('username')];
            $ownerInfo = \Oa::getDataFromApi('get_shop',['manager_sn'=>session('staff_sn')]);
            // $ownerInfo = \Oa::getDataFromApi('get_shop',['manager_sn'=>123]);
            if(isset($ownerInfo['message'][0])){
                $data['shopsn'] = $ownerInfo['message'][0]['shop_sn'];
                session(['shopsn'=>$data['shopsn']]);
            }
            // dump($ownerInfo);
            // dd($data);
            return ['status'=>1,'data'=>$data];
        }else{
            return ['status'=>0,'msg'=>config('hints.130')];
        }

    }

    /**
    *  根据店铺名 获取调动员工信息 
    *  @param    shopsn
    */
    public function getTransferShop(Request $request){
        $input = $request->except('_url');
        // return session('shopsn');

        // return \Oa::getDataFromApi('get_shop',['manager_sn'=>session('staff_sn')]);

        return $this->transferRepos->getTransferShop($input);
        // echo json_decode( $this->transferRepos->getTransferShop($input),true);
    }

    /**
    *  离店签到
    */
    public function outshop(Request $request){
    	$input = $request->except('_url');
    	// $usersn = $request->usersn;
		// $res = $this->transferRepos->updata($params);
    	$params = ['parentid'=>$input['tid'],'time'=>'start_time','status'=>1];
    	$res = $this->transferTimeRecordRepos->save($params);
    	echo json_encode($res,true);
    	
    }

    /**
    *  到店签到  
    */
    public function goshop(Request $request){
    	$input = $request->except('_url');
        if(!isset($input['tid'])) return returnErr('hints.102');
    	// $params = ['parentid'=>$input['tid'],'end_time'=>1];
        // $transList = Transfer::find($input['tid']);
        // if(empty($transList['id'])) return returnErr('hints.102');
        // if(empty($transList['out_shop_time'])) return returnErr('hints.161');
        
    	$params = ['parentid'=>$input['tid'],'time'=>'end_time','status'=>2];
        
    	$res = $this->transferTimeRecordRepos->save($params);
    	// echo json_encode($res,true);
        return $res;
    	// $params = ['id'=>$request->tid,'end_time'=>1];
     //    $res = $this->transferRepos->updata($params);
     //    echo json_encode($res,true);

    }



    /**
    *   店长确认
    */
    public function confirm(Request $request){
       # header("Access-Control-Allow-Origin:*");
        $input = $request->except(['_url', '_token']);
        // return ['status'=>1,'message'=>config('hints.105')];
        // return $input;
        $res = $this->transferRepos->updata($input);
        return $res;
        // return $res?['status'=>1,'message'=>config('hints.105')]:['status'=>0,'message'=>config('hints.106')];
    }

    /**
    *   编辑
    */
    public function edit(Request $request){
        #header("Access-Control-Allow-Origin:*");
        $input = $request->except(['_url', '_token','_','callback']);
        // return ['status'=>1,'message'=>config('hints.105')];
        $res = $this->transferRepos->edit($input);
        $data = json_encode($res);
        return $request->callback.'('.$data.')';
        return $res;
        // return $res?['status'=>1,'message'=>config('hints.105')]:['status'=>0,'message'=>config('hints.106')];
    }

    /**
    *	添加调动单
    */
    public function save(Request $request){
    	#header("Access-Control-Allow-Origin:*");
    	$input = $request->except(['_url', '_token']);
    	$res = $this->transferRepos->save($input);
        $data = json_encode(['err'=>'err']);
        return $request->callback.'('.$data.')';
    	// return $res?['status'=>1,'message'=>config('hints.105')]:['status'=>0,'message'=>config('hints.106')];
    	return $res;
    }

    /**
    *	删除
    */
    public function del(Request $request){
    	header("Access-Control-Allow-Origin:*");
    	$id = $request->input('id');
    	$res = $this->transferRepos->del($id);
    	return $res;
    }


    /**
    *	取消调动
    */
    public function cancel(Request $request){
    	#header("Access-Control-Allow-Origin:*");
    	$input = $request->except(['_url', '_token','_','callback']);
    	$input['status'] = 3;
    	$res = $this->transferRepos->edit($input);
        $data = json_encode(['err'=>'err']);
        return $request->callback.'('.$data.')';
    }

    /**
    *	个人调动记录
    */
    public function record(Request $request){
    	$input = $request->except(['_url', '_token']);
        $input['staff_sn'] = session('staff_sn');
        $params['where'] = [['staff_sn',$input['staff_sn']]];
        // \Crud::getlist($params,new Transfer());
    	// return $input; 这里
    	$res = $this->transferRepos->recordList($input);
    	return $res?['status'=>1,'message'=>config('hints.105'),'data'=>$res]:['status'=>0,'message'=>config('hints.106')];

    }

    /**
    *  是否有调动  
    */
    public function transferstatus(Request $request){
    	$res = $this->transferRepos->transferRecord(session('staff_sn'));
    	return json_encode($res,true);
    }
}
