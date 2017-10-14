<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditorToShopAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_shop', function (Blueprint $table) {
            $table->mediumInteger('auditor_sn')->comment('审核人编号');
            $table->char('auditor_name', 10)->comment('审核人姓名');
            $table->dateTime('audited_at')->nullable()->comment('审核时间');
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
            $table->dropColumn(['auditor_sn', 'auditor_name', 'audited_at']);
        });
    }
}
