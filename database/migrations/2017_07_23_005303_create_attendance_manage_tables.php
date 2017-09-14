<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceManageTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_shop', function (Blueprint $table) {
            $table->increments('id');
            $table->char('shop_sn', 10)->comment('店铺代码');
            $table->char('shop_name', 50)->comment('店铺名称');
            $table->mediumInteger('manager_sn')->unsigned()->comment('店长员工编号');
            $table->char('manager_name', 10)->comment('店长姓名');
            $table->decimal('sales_performance', 10, 2)->comment('销售业绩');
            $table->char('attachment', 100)->comment('附件');
            $table->tinyInteger('status')->unsigned()->comment('状态');
            $table->dateTime('submitted_at')->comment('提交时间');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('attendance_staff_' . date('Ym'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('attendance_shop_id')->unsigned()->comment('店铺考勤表id');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('staff_name', 10)->comment('员工姓名');
            $table->tinyInteger('position')->comment('职务');
            $table->decimal('sales_performance', 10, 2)->comment('销售业绩');
            $table->decimal('working_time', 3, 2)->comment('工作时长（天）');
            $table->decimal('leaving_time', 3, 2)->comment('请假时长（天）');
            $table->decimal('transfering_time', 3, 2)->comment('调动时长（天）');
            $table->tinyInteger('is_missing')->comment('是否漏签(1:是,0:否)');
            $table->tinyInteger('is_late')->comment('是否迟到(1:是,0:否)');
            $table->tinyInteger('is_early_out')->comment('是否早退(1:是,0:否)');
            $table->tinyInteger('is_leaving')->comment('是否请假(1:是,0:否)');
            $table->tinyInteger('is_transfering')->comment('是否调动(1:是,0:否)');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('clock_' . date('Ym'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->default(0)->comment('对应考勤/请假ID');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('shop_sn', 10)->comment('店铺代码');
            $table->decimal('lng', 9, 6)->comment('经度');
            $table->decimal('lat', 9, 6)->comment('纬度');
            $table->char('address', 100)->comment('地址');
            $table->decimal('distance', 5, 2)->unsigned()->comment('到店铺距离');
            $table->tinyInteger('attendance_type')->comment('考勤类型 (1:上班,2:调动,3:请假)');
            $table->tinyInteger('type')->comment('打卡类型(1:开始,2:结束)');
            $table->tinyInteger('is_abandoned')->default(0)->comment('是否作废(1:是,0:否)');
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
        Schema::dropIfExists('attendance_shop');
        Schema::dropIfExists('attendance_staff_' . date('Ym'));
        Schema::dropIfExists('clock_' . date('Ym'));
    }

}
