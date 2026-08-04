<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('geo_radius')->nullable()->after('geo_location');
        });

        if (!DB::table('settings')->where('key', 'geo_radius')->exists()) {
            DB::table('settings')->insert([
                'key' => 'geo_radius',
                'label' => 'Default Geofence Radius (m)',
                'val' => '50'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('geo_radius');
        });
        DB::table('settings')->where('key', 'geo_radius')->delete();
    }
};
