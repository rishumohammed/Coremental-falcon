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
        User::create([
            'type'=>'admin',
            'name'=>'Coremetal Admin',
            'username'=>'admin',
            'password'=>bcrypt('admin#CoreMetal')
        ]);
    }
}
