<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersonalInfoToStaffAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_staff_' . date('Ym'), function (Blueprint $table) {
            $table->smallInteger('staff_position_id')->comment('职位ID');
            $table->char('staff_position', 10)->comment('职位');
            $table->smallInteger('staff_department_id')->comment('部门ID');
            $table->char('staff_department', 20)->comment('部门');
            $table->tinyInteger('staff_status_id')->comment('状态ID');
            $table->char('staff_status', 10)->comment('状态');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_staff_' . date('Ym'), function (Blueprint $table) {
            $table->dropColumn([
                'staff_position_id',
                'staff_position',
                'staff_department_id',
                'staff_department',
                'staff_status_id',
                'staff_status',
            ]);
        });
    }
}
