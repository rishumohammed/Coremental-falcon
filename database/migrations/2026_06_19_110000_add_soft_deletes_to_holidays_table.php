<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToHolidaysTable extends Migration
{
    public function up()
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
