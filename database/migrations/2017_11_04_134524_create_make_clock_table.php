<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMakeClockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clock_patch', function (Blueprint $table) {
            $table->increments('id');
            $table->char('clock_table', 15)->comment('对应的打卡记录表');
            $table->integer('clock_id')->unsigned()->comment('对应的打卡记录ID');
            $table->integer('parent_id')->unsigned()->default(0)->comment('对应考勤/请假ID');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('shop_sn', 10)->comment('店铺代码');
            $table->dateTime('clock_at')->comment('打卡时间');
            $table->dateTime('punctual_time')->nullable()->comment('标准打卡时间');
            $table->tinyInteger('attendance_type')->comment('考勤类型 (1:上班,2:调动,3:请假)');
            $table->tinyInteger('type')->comment('打卡类型(1:开始,2:结束)');
            $table->mediumInteger('operator_sn')->default(0)->comment('代签员工编号');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clock_patch');
    }
}
