<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['user_id' => 'USR001'],
            [
                'role_id' => 1,
                'name' => 'Admin Bengkel',
                'email' => 'admin@bengkelku.com',
                'password' => Hash::make('admin123'),
                'phone_number' => '081111111111',
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['user_id' => 'USR002'],
            [
                'role_id' => 2,
                'name' => 'Kasir Bengkel',
                'email' => 'kasir@bengkelku.com',
                'password' => Hash::make('kasir123'),
                'phone_number' => '082222222222',
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}