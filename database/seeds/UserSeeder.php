<?php

namespace Database\Seeders;
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
            'name'=>'Super Admin',
            'username'=>'superadmin',
            'password'=>bcrypt('Superadmin@123')
        ]);
    }
}
