<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_id' => 1, 'role_name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 2, 'role_name' => 'Kasir', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['role_id' => $role['role_id']],
                $role
            );
        }
    }
}
