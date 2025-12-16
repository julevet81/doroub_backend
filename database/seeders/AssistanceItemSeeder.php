<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssistanceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('assistance_items')->insert([
            [
                'name' => 'بطانية',
                'quantity_in_stock' => 100,
                'code' => 'AI-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'سلة غذائية',
                'quantity_in_stock' => 50,
                'code' => 'AI-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ملابس شتوية',
                'quantity_in_stock' => 75,
                'code' => 'AI-003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'أدوات مدرسية',
                'quantity_in_stock' => 120,
                'code' => 'AI-004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
