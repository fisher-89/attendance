<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('leave_request_id')->comment('请假条id');
            $table->tinyInteger('action')->comment('操作类型：1 创建 2 转交 3 审批 4 驳回 -1 撤销');
            $table->mediumInteger('operator_sn')->comment('操作人');
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
        Schema::dropIfExists('leave_log');
    }
}
