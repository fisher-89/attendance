<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkingTimeToStaffAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_staff_' . date('Ym'), function (Blueprint $table) {
            $table->time('working_start_at')->comment('上班时间');
            $table->time('working_end_at')->comment('下班时间');
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
            $table->dropColumn(['working_start_at', 'working_end_at']);
        });
    }
}
