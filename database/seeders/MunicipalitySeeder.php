<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('municipalities')->insert([
            ['name' => 'بلدية الوادي', 'district_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية البياضة', 'district_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية الرياح', 'district_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية الدبيلة', 'district_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية قمار', 'district_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية حاسي خليفة', 'district_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية أمية ونسة', 'district_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية الطالب العربي', 'district_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية المقرن', 'district_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بلدية الرقيبة', 'district_id' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
