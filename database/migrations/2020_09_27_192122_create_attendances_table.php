<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->boolean('type')->default(0)->comments('0-check_in,1-check_out');
            $table->boolean('entry_type')->default(0)->comments('0-automatic,1-manual');  
            $table->string('photo')->nullable()->comments('required  only if entry_type manual');
            $table->string('lat', 255)->nullable();
            $table->string('lng', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('device', 255)->nullable();
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
        Schema::dropIfExists('attendances');
    }
}
