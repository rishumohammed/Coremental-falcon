<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('name');
            $table->string('username')->unique();                        
            $table->string('password');
            $table->string('geo_location', 50)->nullable();
            $table->string('location')->nullable();
            $table->string('group_id', 50)->nullable();
            
            //Required for salesman
            $table->string('person_id', 50)->nullable();
            $table->json('face_ids')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->string('employee_id')->nullable();
            ///////////////

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
