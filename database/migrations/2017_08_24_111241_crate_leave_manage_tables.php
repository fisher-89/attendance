<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrateLeaveManageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('staff_name', 10)->comment('员工姓名');
            $table->datetime('start_at')->nullable()->comment('开始时间');
            $table->datetime('end_at')->nullable()->comment('结束时间');
            $table->decimal('duration', 5, 2)->comment('请假时长');
            $table->tinyInteger('type_id')->comment('请假类型,关联leave_type表');
            $table->char('reason', 200)->comment('请假原因');
            $table->tinyInteger('status')->comment('请假状态: 0:审批中,1:已通过,-1:已驳回,-2:已撤回');
            $table->tinyInteger('has_clock_out')->comment('已经离开: 0:否,1:是');
            $table->tinyInteger('has_clock_in')->comment('已经返回: 0:否,1:是');
            $table->char('process_instance_id', 50)->comment('钉钉审批实例ID');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leave_type', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->char('name', 10)->comment('名称');
            $table->tinyInteger('sort')->unsigned()->comment('排序');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_type');
    }
}
