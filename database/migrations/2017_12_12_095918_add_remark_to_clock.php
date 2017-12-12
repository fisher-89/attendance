<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarkToClock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('information_schema.TABLES')
            ->where('table_name', 'like', 'clock_20%')
            ->get()->each(function ($model) {
                Schema::table($model->TABLE_NAME, function (Blueprint $table) {
                    $table->char('remark', 100)->default('')->comment('备注');
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('information_schema.TABLES')
            ->where('table_name', 'like', 'clock_20%')
            ->get()->each(function ($model) {
                Schema::table($model->TABLE_NAME, function (Blueprint $table) {
                    $table->dropColumn(['remark']);
                });
            });
    }
}
