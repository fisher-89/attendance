<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferManageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('staff_sn')->unsigned()->comment('员工编号');
            $table->char('staff_name', 10)->comment('员工姓名');
            $table->char('staff_gender', 10)->comment('性别');
            $table->mediumInteger('staff_department_id')->unsigned()->comment('部门ID');
            $table->char('staff_department_name', 10)->comment('部门名称');
            $table->char('current_shop_sn', 10)->comment('当前店铺代码');
            $table->date('leaving_date')->comment('计划出发日期');
            $table->char('leaving_shop_sn', 10)->default('')->comment('调离店铺代码');
            $table->char('leaving_shop_name', 50)->default('')->comment('调离店铺名称');
            $table->datetime('left_at')->nullable()->comment('出发时间');
            $table->char('arriving_shop_sn', 10)->comment('到达店铺代码');
            $table->char('arriving_shop_name', 50)->comment('到达店铺名称');
            $table->tinyInteger('arriving_shop_duty_id')->unsigned()->comment('到达店铺职务id');
            $table->datetime('arrived_at')->nullable()->comment('到达时间');
            $table->tinyInteger('status')->default(0)->comment('调动状态: 0:未出发,1:在途,2:已到达,-1:已取消');
            $table->mediumInteger('maker_sn')->unsigned()->comment('创建人编号');
            $table->char('maker_name', 10)->comment('创建人姓名');
            $table->char('remark', 200)->comment('备注');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transfer_tags', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->char('name', 10)->comment('标签名称');
            $table->tinyInteger('sort')->unsigned()->comment('排序');
        });

        Schema::create('transfer_has_tags', function (Blueprint $table) {
            $table->integer('transfer_id');
            $table->smallInteger('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer');
        Schema::dropIfExists('transfer_tags');
        Schema::dropIfExists('transfer_has_tags');
    }
}
