<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStaff;
use Illuminate\Http\Request;
use DB;

class StatisticsController extends Controller
{
    public function getPersonalReport(Request $request)
    {
        $staffSn = $request->staff_sn;
        $month = $request->month ?: date('Ym');
        $attendanceStaffModel = new AttendanceStaff(['ym' => $month]);
        $response = $attendanceStaffModel
            ->select(
                'staff_sn', 'staff_name',
                DB::raw('SUM(sales_performance_lisha+sales_performance_go+sales_performance_group+sales_performance_partner) AS sales_performance'),
                DB::raw('SUM(working_days) AS working_days'),
                DB::raw('SUM(transferring_days) AS transferring_days'),
                DB::raw('SUM(leaving_days) AS leaving_days'),
                DB::raw('SUM(is_missing) AS is_missing'),
                DB::raw('SUM(late_time) AS late_time'),
                DB::raw('SUM(early_out_time) AS early_out_time'),
                DB::raw('SUM(is_leaving) AS is_leaving'),
                DB::raw('SUM(is_transferring) AS is_transferring'),
                DB::raw('SUM(is_assistor) AS is_assistor'),
                DB::raw('SUM(is_shift) AS is_shift'),
                DB::raw('MIN(status) AS status'),
                'attendance_date'
            )
            ->where('staff_sn', $staffSn)
            ->groupBy(['staff_sn', 'attendance_date'])
            ->orderBy('attendance_date', 'asc')
            ->get();
        return $response;
    }
}
