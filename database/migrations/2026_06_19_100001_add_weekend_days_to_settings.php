<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWeekendDaysToSettings extends Migration
{
    public function up()
    {
        // Only insert if not already present
        if (!DB::table('settings')->where('key', 'weekend_days')->exists()) {
            DB::table('settings')->insert([
                'key'        => 'weekend_days',
                'label'      => 'Weekend Days',
                'val'        => '0,6', // 0 = Sunday, 6 = Saturday
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->where('key', 'weekend_days')->delete();
    }
}
