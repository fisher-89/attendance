<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTdoaSalePerformance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tdoa_sale_performance', function (Blueprint $table) {
            $table->increments('id');
            $table->char('shop_sn', 10)->comment('店铺代码');
            $table->date('attendance_date')->comment('日期');
            $table->decimal('sale_performance', 10, 2)->comment('业绩合计');
            $table->tinyInteger('is_end')->comment('是否办结');
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
        Schema::dropIfExists('tdoa_sale_performance');
    }
}
