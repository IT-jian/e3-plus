<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            'id' => 1,
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => '$2y$10$wBsDO5/ZJmdd4dvl7QqNCuc5kfAyaWYDtxlBlyxhqfcUJMklWm2.a',
        ];

        \DB::table('users')->insert($admin);
    }
}
