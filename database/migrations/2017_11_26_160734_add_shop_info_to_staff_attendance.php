<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopInfoToStaffAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('information_schema.TABLES')
            ->where('table_name', 'like', 'attendance_staff_%')
            ->get()->each(function ($model) {
                Schema::table($model->TABLE_NAME, function (Blueprint $table) {
                    $table->char('shop_sn', 10)->comment('店铺代码');
                    $table->mediumInteger('manager_sn')->unsigned()->comment('店长员工编号');
                    $table->date('attendance_date')->comment('考勤日期');
                    $table->smallInteger('department_id')->comment('部门ID');
                    $table->tinyInteger('status')->comment('状态 0:未提交 1:已提交 2:已通过 -1:已驳回');
                    $table->mediumInteger('auditor_sn')->comment('审核人编号');
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('information_schema.TABLES')
            ->where('table_name', 'like', 'attendance_staff_%')
            ->get()->each(function ($model) {
                Schema::table($model->TABLE_NAME, function (Blueprint $table) {
                    $table->dropColumn(['shop_sn', 'manager_sn', 'attendance_date', 'department_id', 'status', 'auditor_sn']);
                });
            });
    }
}
