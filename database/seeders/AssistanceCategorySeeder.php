<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssistanceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('assistance_categories')->insert([
            ['name' => 'غذاء', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'صحة', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تعليم', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ملابس', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مأوى', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'دعم مالي', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'أنشطة ترفيهية', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
