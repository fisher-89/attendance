<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkingScheduleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('working_schedule_' . date('Ymd'), function (Blueprint $table) {
            $table->char('shop_sn', 10)->comment('店铺代码');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('staff_name', 10)->comment('员工姓名');
            $table->time('clock_in')->nullable()->comment('上班时间');
            $table->time('clock_out')->nullable()->comment('下班时间');
            $table->tinyInteger('shop_duty_id')->unsigned()->default(3)->comment('店铺职务');
            $table->primary(['shop_sn', 'staff_sn']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('working_schedule_' . date('Ymd'));
    }
}
