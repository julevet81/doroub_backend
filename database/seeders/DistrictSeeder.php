<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('districts')->insert([
            ['name' => 'الوادي'],
            ['name' => 'البياضة'],
            ['name' => 'الرباح'],
            ['name' => 'الدبيلة'],
            ['name' => 'قمار'],
            ['name' => 'حاسي خليفه'],
            ['name' => 'اميه ونسه'],
            ['name' => 'الطالب العربي'],
            ['name' => 'المقرن'],
            ['name' => 'الرقيبة'],
        ]);
    }
}
