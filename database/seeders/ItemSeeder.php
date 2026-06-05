<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ── SPAREPART ──────────────────────────────────────────────────────────────
            // SUP001 = PT. IRC Indonesia
            // SUP002 = PT. FDR Tire Indonesia  (Swallow masuk sini juga)
            // SUP003 = PT. Federal Karyatama
            // SUP004 = PT. Yamaha Motor Parts Indonesia
            // SUP005 = PT. Astra Honda Motor Parts
            // SUP006 = PT. NGK Busi Indonesia
            // SUP007 = PT. GS Astra Indonesia
            // SUP008 = PT. Aspira Indonesia
            // SUP009 = Toko Sparepart Umum

            // Ban (category 1)
            ['item_id'=>'ITM001','item_category_id'=>1,'supplier_id'=>'SUP001','item_name'=>'Ban Luar IRC Ring 14',        'purchase_price'=>95000, 'selling_price'=>120000,'stock'=>10],
            ['item_id'=>'ITM002','item_category_id'=>1,'supplier_id'=>'SUP002','item_name'=>'Ban Luar FDR Ring 14',        'purchase_price'=>90000, 'selling_price'=>115000,'stock'=>10],
            ['item_id'=>'ITM003','item_category_id'=>1,'supplier_id'=>'SUP001','item_name'=>'Ban Luar IRC Ring 17',        'purchase_price'=>100000,'selling_price'=>130000,'stock'=>8],

            // Ban Dalam (category 2)
            ['item_id'=>'ITM004','item_category_id'=>2,'supplier_id'=>'SUP001','item_name'=>'Ban Dalam IRC Ring 14',       'purchase_price'=>25000, 'selling_price'=>35000, 'stock'=>15],
            ['item_id'=>'ITM005','item_category_id'=>2,'supplier_id'=>'SUP002','item_name'=>'Ban Dalam Swallow Ring 17',   'purchase_price'=>27000, 'selling_price'=>38000, 'stock'=>12],

            // Pentil (category 3)
            ['item_id'=>'ITM006','item_category_id'=>3,'supplier_id'=>'SUP009','item_name'=>'Pentil Tubeless',             'purchase_price'=>5000,  'selling_price'=>10000, 'stock'=>30],

            // Tambal Ban (category 4)
            ['item_id'=>'ITM007','item_category_id'=>4,'supplier_id'=>'SUP009','item_name'=>'Tip Top Tambal Ban',          'purchase_price'=>8000,  'selling_price'=>15000, 'stock'=>20],
            ['item_id'=>'ITM008','item_category_id'=>4,'supplier_id'=>'SUP009','item_name'=>'Lem Tambal Ban',              'purchase_price'=>5000,  'selling_price'=>10000, 'stock'=>20],

            // Oli Mesin (category 5)
            ['item_id'=>'ITM009','item_category_id'=>5,'supplier_id'=>'SUP003','item_name'=>'Oli Federal Matic 0.8L',      'purchase_price'=>28000, 'selling_price'=>38000, 'stock'=>24],
            ['item_id'=>'ITM010','item_category_id'=>5,'supplier_id'=>'SUP004','item_name'=>'Oli Yamalube Matic 0.8L',     'purchase_price'=>30000, 'selling_price'=>42000, 'stock'=>20],
            ['item_id'=>'ITM011','item_category_id'=>5,'supplier_id'=>'SUP005','item_name'=>'Oli AHM MPX2 0.8L',           'purchase_price'=>32000, 'selling_price'=>45000, 'stock'=>20],

            // Oli Gardan (category 6)
            ['item_id'=>'ITM012','item_category_id'=>6,'supplier_id'=>'SUP004','item_name'=>'Oli Gardan Yamaha 90cc',      'purchase_price'=>12000, 'selling_price'=>20000, 'stock'=>20],

            // Busi (category 7)
            ['item_id'=>'ITM013','item_category_id'=>7,'supplier_id'=>'SUP006','item_name'=>'Busi NGK Standar',            'purchase_price'=>15000, 'selling_price'=>25000, 'stock'=>25],
            ['item_id'=>'ITM014','item_category_id'=>7,'supplier_id'=>'SUP006','item_name'=>'Busi Iridium NGK',            'purchase_price'=>45000, 'selling_price'=>65000, 'stock'=>10],

            // Kampas Rem (category 8)
            ['item_id'=>'ITM015','item_category_id'=>8,'supplier_id'=>'SUP008','item_name'=>'Kampas Rem Depan Beat',       'purchase_price'=>18000, 'selling_price'=>28000, 'stock'=>15],
            ['item_id'=>'ITM016','item_category_id'=>8,'supplier_id'=>'SUP008','item_name'=>'Kampas Rem Belakang Vario',   'purchase_price'=>20000, 'selling_price'=>32000, 'stock'=>12],

            // Aki (category 9)
            ['item_id'=>'ITM017','item_category_id'=>9,'supplier_id'=>'SUP007','item_name'=>'Aki Kering GS Astra 5Ah',    'purchase_price'=>130000,'selling_price'=>170000,'stock'=>8],

            // Lampu (category 10)
            ['item_id'=>'ITM018','item_category_id'=>10,'supplier_id'=>'SUP009','item_name'=>'Bohlam Depan Motor H4 35/35W','purchase_price'=>12000,'selling_price'=>20000,'stock'=>20],
            ['item_id'=>'ITM019','item_category_id'=>10,'supplier_id'=>'SUP009','item_name'=>'Lampu Sein Motor LED',        'purchase_price'=>15000,'selling_price'=>25000,'stock'=>15],

            // CVT (category 11)
            ['item_id'=>'ITM020','item_category_id'=>11,'supplier_id'=>'SUP008','item_name'=>'V-Belt Beat/Vario',          'purchase_price'=>45000, 'selling_price'=>65000, 'stock'=>10],
            ['item_id'=>'ITM021','item_category_id'=>11,'supplier_id'=>'SUP008','item_name'=>'Roller CVT Beat 13g',        'purchase_price'=>22000, 'selling_price'=>35000, 'stock'=>15],

            // Rantai & Gear (category 12)
            ['item_id'=>'ITM022','item_category_id'=>12,'supplier_id'=>'SUP008','item_name'=>'Rantai Motor 428 H 116 Mata','purchase_price'=>55000, 'selling_price'=>75000, 'stock'=>8],
            ['item_id'=>'ITM023','item_category_id'=>12,'supplier_id'=>'SUP008','item_name'=>'Gear Set Honda Supra',       'purchase_price'=>70000, 'selling_price'=>95000, 'stock'=>5],

            // Shockbreaker (category 13)
            ['item_id'=>'ITM024','item_category_id'=>13,'supplier_id'=>'SUP008','item_name'=>'Shock Belakang Beat Standar','purchase_price'=>85000, 'selling_price'=>120000,'stock'=>5],

            // Kabel (category 14)
            ['item_id'=>'ITM025','item_category_id'=>14,'supplier_id'=>'SUP008','item_name'=>'Kabel Gas Motor Universal',  'purchase_price'=>18000, 'selling_price'=>28000, 'stock'=>10],

            // Filter (category 15)
            ['item_id'=>'ITM026','item_category_id'=>15,'supplier_id'=>'SUP008','item_name'=>'Filter Udara Beat/Vario',    'purchase_price'=>18000, 'selling_price'=>28000, 'stock'=>12],

            // Bearing (category 16)
            ['item_id'=>'ITM027','item_category_id'=>16,'supplier_id'=>'SUP008','item_name'=>'Bearing Roda 6201 2RS',      'purchase_price'=>15000, 'selling_price'=>25000, 'stock'=>20],

            // Cairan / Chemical (category 19)
            ['item_id'=>'ITM028','item_category_id'=>19,'supplier_id'=>'SUP009','item_name'=>'Cairan Anti-Bocor Tubeless', 'purchase_price'=>20000, 'selling_price'=>32000, 'stock'=>15],

            // ── JASA (supplier_id = null, stock = null) ───────────────────────────────

            // Jasa Ban (category 20)
            ['item_id'=>'ITM029','item_category_id'=>20,'supplier_id'=>null,'item_name'=>'Tambal Ban Biasa',             'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM030','item_category_id'=>20,'supplier_id'=>null,'item_name'=>'Tambal Ban Tubeless',          'purchase_price'=>0,'selling_price'=>25000,'stock'=>null],
            ['item_id'=>'ITM031','item_category_id'=>20,'supplier_id'=>null,'item_name'=>'Ganti Ban Luar',              'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],
            ['item_id'=>'ITM032','item_category_id'=>20,'supplier_id'=>null,'item_name'=>'Ganti Ban Dalam',             'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM033','item_category_id'=>20,'supplier_id'=>null,'item_name'=>'Pasang Pentil Tubeless',      'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],

            // Jasa Oli (category 21)
            ['item_id'=>'ITM034','item_category_id'=>21,'supplier_id'=>null,'item_name'=>'Ganti Oli Mesin',             'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM035','item_category_id'=>21,'supplier_id'=>null,'item_name'=>'Ganti Oli Gardan',            'purchase_price'=>0,'selling_price'=>5000, 'stock'=>null],

            // Jasa Rem (category 22)
            ['item_id'=>'ITM036','item_category_id'=>22,'supplier_id'=>null,'item_name'=>'Ganti Kampas Rem Depan',      'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],
            ['item_id'=>'ITM037','item_category_id'=>22,'supplier_id'=>null,'item_name'=>'Ganti Kampas Rem Belakang',   'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],
            ['item_id'=>'ITM038','item_category_id'=>22,'supplier_id'=>null,'item_name'=>'Setel Rem',                   'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],

            // Jasa CVT (category 23)
            ['item_id'=>'ITM039','item_category_id'=>23,'supplier_id'=>null,'item_name'=>'Servis CVT',                  'purchase_price'=>0,'selling_price'=>50000,'stock'=>null],
            ['item_id'=>'ITM040','item_category_id'=>23,'supplier_id'=>null,'item_name'=>'Ganti V-Belt',               'purchase_price'=>0,'selling_price'=>20000,'stock'=>null],
            ['item_id'=>'ITM041','item_category_id'=>23,'supplier_id'=>null,'item_name'=>'Ganti Roller CVT',           'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],

            // Jasa Kelistrikan (category 24)
            ['item_id'=>'ITM042','item_category_id'=>24,'supplier_id'=>null,'item_name'=>'Ganti Busi',                  'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM043','item_category_id'=>24,'supplier_id'=>null,'item_name'=>'Ganti Lampu',                 'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM044','item_category_id'=>24,'supplier_id'=>null,'item_name'=>'Cek & Ganti Aki',             'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],

            // Jasa Rantai (category 25)
            ['item_id'=>'ITM045','item_category_id'=>25,'supplier_id'=>null,'item_name'=>'Setel Rantai',                'purchase_price'=>0,'selling_price'=>10000,'stock'=>null],
            ['item_id'=>'ITM046','item_category_id'=>25,'supplier_id'=>null,'item_name'=>'Ganti Rantai',               'purchase_price'=>0,'selling_price'=>20000,'stock'=>null],
            ['item_id'=>'ITM047','item_category_id'=>25,'supplier_id'=>null,'item_name'=>'Ganti Gear Set',             'purchase_price'=>0,'selling_price'=>25000,'stock'=>null],

            // Jasa Shockbreaker (category 26)
            ['item_id'=>'ITM048','item_category_id'=>26,'supplier_id'=>null,'item_name'=>'Ganti Shockbreaker Belakang', 'purchase_price'=>0,'selling_price'=>30000,'stock'=>null],
            ['item_id'=>'ITM049','item_category_id'=>26,'supplier_id'=>null,'item_name'=>'Ganti Seal Shock',           'purchase_price'=>0,'selling_price'=>40000,'stock'=>null],

            // Jasa Servis Ringan (category 27)
            ['item_id'=>'ITM050','item_category_id'=>27,'supplier_id'=>null,'item_name'=>'Servis Ringan',               'purchase_price'=>0,'selling_price'=>30000,'stock'=>null],

            // Jasa Pemasangan (category 28)
            ['item_id'=>'ITM051','item_category_id'=>28,'supplier_id'=>null,'item_name'=>'Jasa Pasang Aksesoris',       'purchase_price'=>0,'selling_price'=>15000,'stock'=>null],

            // Jasa Pemeriksaan (category 29)
            ['item_id'=>'ITM052','item_category_id'=>29,'supplier_id'=>null,'item_name'=>'Pemeriksaan & Diagnosa',      'purchase_price'=>0,'selling_price'=>20000,'stock'=>null],
        ];

        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(
                ['item_id' => $item['item_id']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
