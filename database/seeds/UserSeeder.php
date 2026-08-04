<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['username' => 'coreadmin'],
            [
                'type' => 'admin',
                'name' => 'Core Admin',
                'password' => bcrypt('C0readm1n#098')
            ]
        );
    }
}
