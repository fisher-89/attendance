<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;

class CrudService{

	//*****   特别注意   ******//
	/*凌晨4点至次日凌晨4点为一个工作日
		86400 1日
		14400 4小时
		100800  
	*/


	/** 获取数据
	*	$params['where']     array  eg: $params['where'] = [['id',49],['age',19]];
	*   $params['selects']   array  eg: $params['selects'] = ['id','age'];
	*   $params['orderby']   array  eg: $params['orderby'] = ['created','desc'];
	*/
	public function getlist($params,$model){
		if(!isset($model) && !is_string($model)) return false;
		$take = $params['take']??10;
		$skip = $params['skip']??0;
		$selects = $params['selects']??0;
		$wheres = $params['where']??0;
		$orderby = $params['orderby']??0;


		// return $model::when($wheres,function($query) use ($params){
		// 	return $query->where($params['where']);
		// })->select(['id','staff_sn'])->take($take)->skip($skip)->orderBy('created_at','desc')->get();


		return $model::when($wheres,function($query) use ($params){
			return $query->where($params['where']);
		})->when($selects , function($query) use ($params){
			return $query->select($params['selects']);
		})->take($take)->skip($skip)->when($orderby , function($query) use ($params){
			if(isset($params['orderby'])){
				return $query->orderBy($params['orderby'][0],$params['orderby'][1]);
			}
				return $query->orderBy('created_at','desc');			
		})->get();


		// if(isset($params['where'])){
		// 	return $model::where($params['where'])->take($take)->skip($skip)->orderBy('created_at','desc')->get();
		// 	// return $model::where([['parent','41']])->get();

		// };

		// return $model::take($take)->skip($skip)->orderBy('created_at','desc')->get();
	}

	/** 数据保存 保存一条
	*	$input   数据
	*	$model   模型
	*/
	public function save($input,$model){
		return $model::create($input);
	}

	/** 数据保存 保存多条
	*	$input   数据
	*	$dbname  表名
	*/
	public function saveList($input,$dbname){
		return DB::table($dbname)->insert($input);
	}

	/** 更新数据  待完成
	 *  $params 
	 *  $model
	 */
	public function updata($params,$model){

	}



	/** 删除数据
	*   $params   条件
	*	$model    模型
	*/
	public function update($params,$model){
		$obj = $model::find($params['id']);
		if(!$obj){
			return ['msg'=>'errs'];
		}
		$obj->deleted_at = date('Y-m-d H:i:s',time());
		$res = $obj->save();
		return isset($res)?['msg'=>'ok']:['msg'=>'err'];
	}
}