<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_id'   => 'SUP001',
                'supplier_name' => 'PT. IRC Indonesia',
                'phone_number'  => '021-5551001',
                'address'       => 'Jakarta Barat',
            ],
            [
                'supplier_id'   => 'SUP002',
                'supplier_name' => 'PT. FDR Tire Indonesia',
                'phone_number'  => '021-5551002',
                'address'       => 'Tangerang',
            ],
            [
                'supplier_id'   => 'SUP003',
                'supplier_name' => 'PT. Federal Karyatama',
                'phone_number'  => '021-5551003',
                'address'       => 'Jakarta Utara',
            ],
            [
                'supplier_id'   => 'SUP004',
                'supplier_name' => 'PT. Yamaha Motor Parts Indonesia',
                'phone_number'  => '021-5551004',
                'address'       => 'Bekasi',
            ],
            [
                'supplier_id'   => 'SUP005',
                'supplier_name' => 'PT. Astra Honda Motor Parts',
                'phone_number'  => '021-5551005',
                'address'       => 'Jakarta Selatan',
            ],
            [
                'supplier_id'   => 'SUP006',
                'supplier_name' => 'PT. NGK Busi Indonesia',
                'phone_number'  => '021-5551006',
                'address'       => 'Depok',
            ],
            [
                'supplier_id'   => 'SUP007',
                'supplier_name' => 'PT. GS Astra Indonesia',
                'phone_number'  => '021-5551007',
                'address'       => 'Jakarta Timur',
            ],
            [
                'supplier_id'   => 'SUP008',
                'supplier_name' => 'PT. Aspira Indonesia',
                'phone_number'  => '021-5551008',
                'address'       => 'Bogor',
            ],
            [
                'supplier_id'   => 'SUP009',
                'supplier_name' => 'Toko Sparepart Umum',
                'phone_number'  => '0812-0001111',
                'address'       => 'Lokal',
            ],
        ];

        foreach ($suppliers as $sup) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_id' => $sup['supplier_id']],
                array_merge($sup, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
