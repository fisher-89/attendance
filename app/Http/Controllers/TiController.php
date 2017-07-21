<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DemoRepositories;
use Illuminate\Support\Facades\Redis;
use App\Services\CrudService;
use App\Models\Attendance_staff;
use App\Services\OAService;


// use App\Traits\DemoTrait;

class TiController extends Controller
{
	public $demoRepos;
    public $CrudSer;
    public function __construct(DemoRepositories $demoRepos , CrudService $CrudSer){
    	$this->demoRepos = $demoRepos;
        $this->CrudSer = $CrudSer;
    }

    public function t(Request $request){
    	// dd( $request->all());
    	$url = $request->_url;
    	$urlArr = explode('/',$url);
    	dd($urlArr);
    }


    public function oaser(OAService $oaser){
        // dd(session('staff_sn'));
        return $oaser->getDataFromApi('get_user',['staff_sn'=>session('staff_sn')]);

        return session()->all();
    }

    public function valisave(Request $request){
        // return response( )->header('X-Header-One', 'Header Value')->header('X-Header-Two', 'Header Value');
         return response('Hello World', 200)
                  ->header('Content-Type', 'text/plain');
            
        return $request->all();
    }

    public function fer(){
        return \Fer::get();
    }

    public function crud(Request $request){
        $staff_sn = 41;
       $params['where'] = [['parent',$staff_sn]];

        $res = \Crud::getlist($params,new Attendance_staff);
        // dd();
        echo json_encode($res);
        // return $Crud->rdd();
    }

    public function save(Request $request){

    	$data = $request->except('_url');
    	$imgRes = upImage($data['thumb']);

    	if($imgRes['strleng'] > 0 ){
    		$data['thumb'] = $imgRes['filename'];
    		$res = $this->demoRepos->save($data);
    	}    	

    	echo json_encode($res,true);
    }

    public function try(Request $request){
        $input = $request->except(['_url','_token']);
        // $input = ['uname'=>'fu','b'=>'dfkj'];
        // return $input;
        return $this->CrudSer->save($input,'App\Models\\'.$input['king']);
        DemoTrait::ea();
        // dd($input); 
        return $input;
    }

    public function trydel(){
        $params['id']= 60;
        return $this->CrudSer->update($params,new Demo());
    }

    public function redis(){
        // $user = Redis::get('liu');

        $user = Redis::lrange('rpush',0,10);
        if( !isset($user) || (count($user) <= 0) ){
            return ['msg'=>'error'];
        }
        // dd(json_decode(json_encode($user[0]),true));
        // $res = json_decode($user[0]);
        // $input['uname'] = $res->uname;

        foreach ($user as $key => $value) {
            $tmpRes = json_decode($value);
            $input[]['uname'] = $tmpRes->uname;
        }
        // $res = $this->CrudSer->saveList(['uname'=>'liu'],'redis');
        $res = $this->CrudSer->saveList($input,'redis');

        foreach ($user as $key => $value) {
            $lpop = Redis::lpop('rpush');
        }

        dd($lpop);

        // $lengh = Redis::llen('rpush');
       

        for($i=0;$i<$lpop;$i++){

        }
    }

    public function redissave(Request $request){
        $input = $request->except('_url');
        // $res = Redis::rpush('uname:4',$input['uname']);
        // $res = Redis::hmset('unamehaxi','uname','liu','age',19);
        
        // for($i=0;$i<5000;$i++){}
            $dataArr = [
            'uname'=>'刘勇勇'.uniqid().rand(100,100000),
            'time'=>date('Y-m-d H:i:s',time()),
            'age' =>rand(100,1000),
            ];
            $res = Redis::rpush('rpush',json_encode($dataArr,JSON_UNESCAPED_UNICODE));
        
       
        return $res;
    }
}
