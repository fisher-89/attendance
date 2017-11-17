<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClockChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clock_change_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('clock_id')->comment('打卡id');
            $table->mediumInteger('ym')->comment('打卡分表标识');
            $table->tinyInteger('action')->comment('操作类型 1:创建 2:恢复 3:编辑 -1:作废');
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
        Schema::dropIfExists('clock_change_log');
    }
}
