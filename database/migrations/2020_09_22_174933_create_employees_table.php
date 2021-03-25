<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();            
            $table->string('name');
            $table->string('employee_id', 50)->nullable();
            $table->string('person_id', 50)->nullable();
            $table->json('face_ids')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_salesman')->default(false);
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
        Schema::dropIfExists('employees');
    }
}
