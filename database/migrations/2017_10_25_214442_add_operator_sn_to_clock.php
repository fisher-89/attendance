<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOperatorSnToClock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clock_' . date('Ym'), function (Blueprint $table) {
            $table->mediumInteger('operator_sn')->default(0)->comment('代签员工编号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clock_' . date('Ym'), function (Blueprint $table) {
            $table->dropColumn(['operator_sn']);
        });
    }
}
