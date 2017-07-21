<?php
namespace App\Services;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ExcelService{

public function importFn($file_tmp_path){
	// $inpRes = $request->except(['_url']);
	$fieldArr = [
        '完成'=>1,
        '同意'=>1,
        '拒绝'=>0,
    ];

    // $filePath = $_FILES['files']['tmp_name'];

    $exlHandle = Excel::load($file_tmp_path)->get()->toArray();

    $newArr = [];
    foreach ($exlHandle as $key => $value) {
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
    		'end_time'=>$value['结束时间'],
                // 'holiday_detail'=>$value['请假事由'],
    		'holiday_detail'=>isset($value['请假事由'])?$value['请假事由']:'',
                // 'subject_result'=>$value['审批结果']
    	]);
    }
    return DB::table('holiday')->insert($newArr);

    // echo json_encode($insertRes,true);
}


}