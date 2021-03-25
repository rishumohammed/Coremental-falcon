<?php

use Illuminate\Database\Seeder;
use App\Setting;
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key'=>'confidence_threshold',
            'label'=>'Face recognition confidence threshold',
            'val'=>'55'
        ]);

        Setting::create([
            'key'=>'timezone',
            'label'=>'Timezone',
            'val'=>'Asia/Dubai'
        ]);
    }
}
