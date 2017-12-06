<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarkToShopAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_shop', function (Blueprint $table) {
            $table->char('manager_remark', 200)->comment('店长备注');
            $table->char('auditor_remark', 200)->comment('人事备注');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_shop', function (Blueprint $table) {
            $table->dropColumn(['manager_remark', 'auditor_remark']);
        });
    }
}
