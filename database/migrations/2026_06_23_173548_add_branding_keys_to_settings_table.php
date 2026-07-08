<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::table('settings')->insert([
            ['key' => 'app_logo', 'label' => 'Application Logo', 'val' => ''],
            ['key' => 'app_fallback_text', 'label' => 'Fallback Text', 'val' => 'Falcon'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('settings')->whereIn('key', ['app_logo', 'app_fallback_text'])->delete();
    }
};
