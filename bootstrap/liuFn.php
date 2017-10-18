<?php

// 遍历创建文件夹
function mkDirs($dir)
{
    if (!is_dir($dir)) {
        if (!mkDirs(dirname($dir))) {
            return false;
        }
        if (!mkdir($dir, 0777)) {
            return false;
        }
    }
    return true;
}

//随机数
function randChar($length)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

/**
 * 改变数组键名
 * @param  array  数组源
 * @param  string 键名
 */
function array_specifyKey($arr, $setKey)
{
    $newArr = [];
    if (!isset($arr[0][$setKey])) {
        throw new Exception("Check the specified key");
    }

    foreach ($arr as $key => $value) {
        $newArr[$value[$setKey]] = $value;
    }
    return $newArr;
}

/**
 * 未启用
 * 根据键名 将数据压入数组
 * @param  array  数据源
 * @param  string 键名
 */
function array_pushData($arr, $keyName)
{
    foreach ($arr as $key => $value) {
        # code...
    }
}

//上传图片
function upImage($file)
{

    $str = explode(',', $file);
    $start = strpos($str[0], '/');
    $end = strpos($str[0], ';');
    $extension = substr($str[0], $start + 1, $end - $start - 1);

    $start = strpos($file, ',');
    $imgorigin = substr($file, $start + 1);

    $imgFile = base64_decode($imgorigin);
    $curTime = time();
    $rootPath = public_path();
    $filepath = '/upfile/' . Date('Y', $curTime) . '/' . Date('Y-m', $curTime) . '/' . Date('Y-m-d', $curTime);
    mkDirs($rootPath . $filepath);
    $filename = $filepath . '/' . randChar(10) . uniqid() . '.' . $extension;

    $res['strleng'] = file_put_contents($rootPath . $filename, $imgFile);
    $res['filename'] = $filename;

    return $res;
}

//返回提示
function returnRes($res, $hintsOk, $hintsFail)
{
    if (is_object($res)) {
        return isset($res[0]) ? ['status' => 1, 'msg' => config($hintsOk), 'data' => $res] : ['status' => 0, 'msg' => config($hintsFail)];
    } else {
        return $res ? ['status' => 1, 'msg' => config($hintsOk)] : ['status' => 0, 'msg' => config($hintsFail)];
    }

}

//返回错误
function returnErr($msg)
{
    return ['status' => 0, 'msg' => config($msg)];
}

// //返回
// function returnRes($params){
//     // isset($data[0])?['status'=>1,'msg'=>config('hints.101'),'data'=>$data[0]]:['status'=>0,'msg'=>config('hints.103')];
//     return $params['obj']?['status'=>1,'msg'=>config($params['hints']):?['status'=>1,'msg'=>config($params['hints']);
// }

function edd($params)
{
    echo $params;
}

if (!function_exists('source')) {

    function source($path, $secure = null)
    {
        $realPath = public_path($path);
        if (file_exists($realPath)) {
            $path .= '?ver=' . date('md.H.i', filemtime($realPath));
        }
        return app('url')->asset($path, $secure);
    }

}
