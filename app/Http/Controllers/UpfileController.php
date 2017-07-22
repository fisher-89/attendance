<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DemoRepositories;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class UpfileController extends Controller
{
    //
    protected $demoRepos;
    public function __construct(DemoRepositories $demoRepos){
    	$this->demoRepos = $demoRepos;
    }

    public function up(Request $request){
    	$inpRes = $request->except(['_url']);
        $file = upImage($inpRes['imgs']);
        $save['uname'] = 'liu';
    	// $save['thumb'] = $file['filename'];
        $img = Image::make('./'.$file['filename'])->resize(300, 200);

        return $img;
    	$res = $this->demoRepos->save($save);
    	echo json_encode($res,true);
    }

    /** wangEditor demo
    *   
    */
    public function wang(Request $request){

        $files = $_FILES['upfilename']['tmp_name'];
        $ext = substr($_FILES['upfilename']['name'], strripos($_FILES['upfilename']['name'], '.'));
        $filename = time().$ext;
        $img = Image::make($files)->resize(300, 200)->save($filename);
        echo '/'.$filename;
        // echo 'error|服务器端错误'.json_encode();
        // echo '/upfile/2017/2017-03/2017-03-22/q1WGG7OzIC58d24a0502d850.png';
        #return 'error|服务器端错误'.json_encode($_FILES);
    }

    public function exl(Request $request){
        $inpRes = $request->except(['_url']);

        $fieldArr = [
            '完成'=>1,
            '同意'=>1,
            '拒绝'=>0,
        ];

        $filePath = $_FILES['files']['tmp_name'];
         // $filePath = 'upfile/11111.xls';
        // $filePath = $filePath;
        $res = Excel::load($filePath)->get()->toArray();
        $newArr = [];
        foreach ($res as $key => $value) {
            # code...
            if($value['审批状态'] == '已撤销') break;
            array_push($newArr, [
                'sponsor'=>$value['发起人工号'],
                'sponsor_name'=>$value['发起人姓名'],
                'title'=>$value['标题'],
                'subject_status'=>$fieldArr[$value['审批状态']],
                'subject_result'=>$fieldArr[$value['审批结果']],
                'subject_name'=>$value['历史审批人姓名'],
                'department'=>$value['发起人部门'],
                'holiday_type'=>isset($value['请假类型'])?$value['请假类型']:'',
                'start_time'=>$value['开始时间'],
                'ent_time'=>$value['结束时间'],
                // 'holiday_detail'=>$value['请假事由'],
                'holiday_detail'=>isset($value['请假事由'])?$value['请假事由']:'',
                // 'subject_result'=>$value['审批结果']

                ]);
        }
        $res = DB::table('holiday')->insert($newArr);
        dd($newArr);
        echo json_encode($res,true);
       
        // $_FILES['files']['tmp_name'];
      
         
    }


    public function export(Request $request){
        $cellData = [
            ['学号','姓名','成绩'],
            ['10001','AAAAA','99'],
            ['10002','BBBBB','92'],
            ['10003','CCCCC','95'],
            ['10004','DDDDD','89'],
            ['10005','EEEEE','96'],
        ];
        // echo time().'<br>';
        // echo substr(time(),-1);
        // echo substr(strrev(time()),0,8);
        // $randName = substr(time(),5);
        $excelName = '学生成绩_'.substr(time(),5);

        $data = json_encode(['err'=>'err']);
        // return $request->callback.'('.$data.')';

        Excel::create($excelName,function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->download('xls');


    }
}
