<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Sparepart (item_type_id = 1)
            ['item_category_id' => 1,  'item_type_id' => 1, 'category_name' => 'Ban'],
            ['item_category_id' => 2,  'item_type_id' => 1, 'category_name' => 'Ban Dalam'],
            ['item_category_id' => 3,  'item_type_id' => 1, 'category_name' => 'Pentil'],
            ['item_category_id' => 4,  'item_type_id' => 1, 'category_name' => 'Tambal Ban'],
            ['item_category_id' => 5,  'item_type_id' => 1, 'category_name' => 'Oli Mesin'],
            ['item_category_id' => 6,  'item_type_id' => 1, 'category_name' => 'Oli Gardan'],
            ['item_category_id' => 7,  'item_type_id' => 1, 'category_name' => 'Busi'],
            ['item_category_id' => 8,  'item_type_id' => 1, 'category_name' => 'Kampas Rem'],
            ['item_category_id' => 9,  'item_type_id' => 1, 'category_name' => 'Aki'],
            ['item_category_id' => 10, 'item_type_id' => 1, 'category_name' => 'Lampu'],
            ['item_category_id' => 11, 'item_type_id' => 1, 'category_name' => 'CVT'],
            ['item_category_id' => 12, 'item_type_id' => 1, 'category_name' => 'Rantai & Gear'],
            ['item_category_id' => 13, 'item_type_id' => 1, 'category_name' => 'Shockbreaker'],
            ['item_category_id' => 14, 'item_type_id' => 1, 'category_name' => 'Kabel'],
            ['item_category_id' => 15, 'item_type_id' => 1, 'category_name' => 'Filter'],
            ['item_category_id' => 16, 'item_type_id' => 1, 'category_name' => 'Bearing'],
            ['item_category_id' => 17, 'item_type_id' => 1, 'category_name' => 'Baut & Mur'],
            ['item_category_id' => 18, 'item_type_id' => 1, 'category_name' => 'Aksesoris'],
            ['item_category_id' => 19, 'item_type_id' => 1, 'category_name' => 'Cairan / Chemical'],

            // Jasa (item_type_id = 2)
            ['item_category_id' => 20, 'item_type_id' => 2, 'category_name' => 'Jasa Ban'],
            ['item_category_id' => 21, 'item_type_id' => 2, 'category_name' => 'Jasa Oli'],
            ['item_category_id' => 22, 'item_type_id' => 2, 'category_name' => 'Jasa Rem'],
            ['item_category_id' => 23, 'item_type_id' => 2, 'category_name' => 'Jasa CVT'],
            ['item_category_id' => 24, 'item_type_id' => 2, 'category_name' => 'Jasa Kelistrikan'],
            ['item_category_id' => 25, 'item_type_id' => 2, 'category_name' => 'Jasa Rantai'],
            ['item_category_id' => 26, 'item_type_id' => 2, 'category_name' => 'Jasa Shockbreaker'],
            ['item_category_id' => 27, 'item_type_id' => 2, 'category_name' => 'Jasa Servis Ringan'],
            ['item_category_id' => 28, 'item_type_id' => 2, 'category_name' => 'Jasa Pemasangan'],
            ['item_category_id' => 29, 'item_type_id' => 2, 'category_name' => 'Jasa Pemeriksaan'],
        ];

        foreach ($categories as $cat) {
            DB::table('item_categories')->updateOrInsert(
                ['item_category_id' => $cat['item_category_id']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
