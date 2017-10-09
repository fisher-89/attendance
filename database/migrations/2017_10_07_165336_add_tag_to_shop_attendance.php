<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagToShopAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_shop', function (Blueprint $table) {
            $table->tinyInteger('is_missing')->comment('是否漏签(1:是,0:否)');
            $table->tinyInteger('is_late')->comment('是否迟到(1:是,0:否)');
            $table->tinyInteger('is_early_out')->comment('是否早退(1:是,0:否)');
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
            $table->dropColumn(['is_missing', 'is_late', 'is_early_out']);
        });
    }
}
