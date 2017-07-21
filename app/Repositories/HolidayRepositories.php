<?php 
namespace App\Repositories;

use App\Models\Holiday;
use App\Services\PluginService;
use App\Services\ExcelService;

class HolidayRepositories{

	public function getlist($request){
		$plugin = new PluginService;
		return  $plugin->dataTables($request,new Holiday());
	}

	public function cancel($id){
		if(!isset($id)) return returnErr('hints.102');
		$findRes = Holiday::find($id);
		if(!$findRes['id']) return returnErr('hints.100');
		$findRes->subject_status = '2';
		$saveRes = $findRes->save();

		return returnRes($saveRes,'hints.125','hints.126');
		return isset($saveRes)?['status'=>0,'msg'=>'hints.125']:['status'=>0,'msg'=>'hints.126'];
	}

	public function importFn($_files){
		$acceptType = 'application/octet-stream';
		if($_files['type'] != $acceptType) return returnErr('hints.140');
		
		$excel = new ExcelService;
		return $excel->importFn($_files['tmp_name']);
	}
}