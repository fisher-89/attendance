<?php 
namespace App\Repositories;
use App\Models\Demo;

class DemoRepositories{

	public function save($input){
		// return $input;
		$res =  Demo::create($input);
		return $res;
		return $res['id']?['status'=>1]:['status'=>0];
	}
}