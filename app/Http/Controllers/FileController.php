<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function uploadTmpFile(Request $request)
    {
        if ($request->hasFile('tmp_file') && $request->file('tmp_file')->isValid()) {
            $fileName = date('YmdHis') . app('CurrentUser')->staff_sn . mt_rand(1000, 9999) . '.' . $request->tmp_file->extension();
            $path = $request->file('tmp_file')->storeAs('/tmp', $fileName, 'public');
        }
        return '/storage/' . $path;
    }
}
