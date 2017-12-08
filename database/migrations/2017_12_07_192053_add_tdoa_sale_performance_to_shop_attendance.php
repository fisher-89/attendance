<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTdoaSalePerformanceToShopAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_shop', function (Blueprint $table) {
            $table->decimal('tdoa_sales_performance', 10, 2)->comment('外部汇总表业绩');
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
            $table->dropColumn(['tdoa_sales_performance']);
        });
    }
}
