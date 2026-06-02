<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['item_type_id' => 1, 'type_name' => 'Sparepart', 'created_at' => now(), 'updated_at' => now()],
            ['item_type_id' => 2, 'type_name' => 'Jasa', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($types as $type) {
            DB::table('item_types')->updateOrInsert(
                ['item_type_id' => $type['item_type_id']],
                $type
            );
        }
    }
}
