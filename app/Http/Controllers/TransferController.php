<?php

namespace App\Http\Controllers;

use App\Repositories\TransferRepositories;
use Illuminate\Http\Request;

class TransferController extends Controller
{

    public $transferRepos;

    public function __construct(TransferRepositories $transferRepos)
    {
        $this->transferRepos = app('TransferRepos');
    }

    /**
     *  获取员工的调动信息
     */
    public function getTransferByStaff(Request $request)
    {
        $staffSn = $request->has('staff_sn') ? $request->get('staff_sn') : app('CurrentUser')->staff_sn;
        return $this->transferRepos->getRecordByStaff($staffSn, $request);
    }

    /**
     *  获取待执行的调动
     */
    public function getNextTransfer(Request $request)
    {
        $staffSn = $request->has('staff_sn') ? $request->get('staff_sn') : app('CurrentUser')->staff_sn;
        return $this->transferRepos->getNextRecord($staffSn);
    }

    /**
     * 调动打卡
     * @param Request $request
     * @return mixed
     */
    public function save(Request $request)
    {
        return $this->transferRepos->clock($request->get('parent_id'), $request);
    }

}
