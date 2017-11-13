<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssistorToStaffAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_staff_' . date('Ym'), function (Blueprint $table) {
            $table->tinyInteger('is_assistor')->default(0)->comment('是否为协助 1：是 0：否');
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
            $table->dropColumn(['is_assistor']);
        });
    }
}
