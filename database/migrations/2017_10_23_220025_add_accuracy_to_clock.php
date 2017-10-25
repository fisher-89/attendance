<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccuracyToClock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clock_' . date('Ym'), function (Blueprint $table) {
            $table->smallInteger('accuracy')->comment('定位精度');
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
            $table->dropColumn(['accuracy']);
        });
    }
}
