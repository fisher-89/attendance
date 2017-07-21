<?php
namespace App\Services;
use Intervention\Image\Facades\Image;

class UpfileService{

	public function upimg($origin){
		$imgCode = $origin;
		 
	}

	/**上传图片 生成缩略图
	*  @params $origin  string  base64 字符串
	*  @params $sizeArr Array   缩略图尺寸
	*/

	public function toImg($origin,Array $sizeArr){

		if(!is_array($sizeArr) || !isset($sizeArr[0])){
			return ['err'=>returnErr('hints.140')];
		}

		$start = stripos($origin, '/');
		$end = stripos($origin, ';');
		$extension = substr($origin, $start+1,$end-$start-1);

		$start = stripos($origin,','); 
		$img = base64_decode(substr($origin,$start+1));
		
		$tmpPath = './upfile/'.date('Y').'/'.date('Y-m').'/'.date('Y-m-d').'/';
		$createPath = mkDirs($tmpPath);
		$fileName = randChar(10).uniqid();
				
		if(!$createPath){
			return ['msg'=>'err'];
		}

		foreach ($sizeArr as $key => $sizeVal) {
			
			$origin = Image::make($img);
			$width = $origin->width();
			$height = $origin->height();
			$savePath[$key] = $tmpPath.$fileName.$key.'.'. $extension;
			
			if($width<$sizeVal){
				$origin->resize($width,$height)->save($savePath[$key]);
				break;
			}

			$scale = $width/$sizeVal;
			$origin->resize($width/$scale,$height/$scale)->save($savePath[$key]);
			// $origin->resize($width/$scale,$height/$scale)->save($savePath[$key]);

		}

		return isset($savePath)?$savePath:returnErr('hints.140');

	}


}