<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('donors')->insert([
            [
                'full_name' => 'شركة الخير',
                'activity' => 'تبرعات غذائية',
                'phone' => '0550123456',
                'assistance_category_id' => 1,
                'description' => 'تساهم في دعم الأسر المحتاجة بالغذاء والمستلزمات الأساسية.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'جمعية الأمل',
                'activity' => 'دعم صحي',
                'phone' => '0550654321',
                'assistance_category_id' => 2,
                'description' => 'تقدم المساعدات الطبية والدوائية للمستفيدين.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'مؤسسة العطاء',
                'activity' => 'توفير ملابس',
                'phone' => '0550987654',
                'assistance_category_id' => 1,
                'description' => 'تبرعات ملابس شتوية وصيفية للأطفال والعائلات.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'شركة الرعاية',
                'activity' => 'دعم تعليمي',
                'phone' => '0550333444',
                'assistance_category_id' => 3,
                'description' => 'توفير أدوات مدرسية ومساعدات تعليمية للطلاب المحتاجين.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
