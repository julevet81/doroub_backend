<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VolunteerSeeder extends Seeder
{
    public function run(): void
    {
         DB::table('volunteers')->insert([
            [
                'full_name' => 'أحمد بن علي',
                'membership_id' => 'V-1001',
                'gender' => 'male',
                'email' => 'ahmed@example.com',
                'phone_1' => '0550123456',
                'phone_2' => '0550654321',
                'address' => 'الجزائر العاصمة',
                'date_of_birth' => '1990-05-15',
                'national_id' => '1234567890123',
                'joining_date' => '2020-01-10',
                'subscriptions' => 5000.00,
                'skills' => 'إدارة المشاريع, التبرعات',
                'study_level' => 'bachelor',
                'grade' => 'active',
                'section' => 'management',
                'notes' => 'مشارك نشط في الأنشطة السنوية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'فاطمة الزهراء سعيد',
                'membership_id' => 'V-1002',
                'gender' => 'female',
                'email' => 'fatima@example.com',
                'phone_1' => '0550987654',
                'phone_2' => null,
                'address' => 'وهران',
                'date_of_birth' => '1995-08-20',
                'national_id' => '9876543210987',
                'joining_date' => '2021-06-05',
                'subscriptions' => 2500.00,
                'skills' => 'التواصل الاجتماعي, العلاقات العامة',
                'study_level' => 'master',
                'grade' => 'active',
                'section' => 'relations',
                'notes' => 'تعمل على تنسيق الحملات الاجتماعية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'محمد الطاهر',
                'membership_id' => 'V-1003',
                'gender' => 'male',
                'email' => null,
                'phone_1' => '0550111222',
                'phone_2' => null,
                'address' => 'قسنطينة',
                'date_of_birth' => '1985-12-01',
                'national_id' => '1122334455667',
                'joining_date' => '2019-09-12',
                'subscriptions' => 10000.00,
                'skills' => 'التخطيط المالي, الإدارة',
                'study_level' => 'phd',
                'grade' => 'founder',
                'section' => 'finance',
                'notes' => 'مؤسس ومساهم رئيسي في الجمعية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'ليلى حسين',
                'membership_id' => 'V-1004',
                'gender' => 'female',
                'email' => 'leila@example.com',
                'phone_1' => '0550333444',
                'phone_2' => '0550555666',
                'address' => 'عنابة',
                'date_of_birth' => '2000-03-10',
                'national_id' => '5566778899001',
                'joining_date' => '2022-02-01',
                'subscriptions' => 0.00,
                'skills' => 'التسويق والإعلام',
                'study_level' => 'bachelor',
                'grade' => 'honorary',
                'section' => 'media',
                'notes' => 'عضو شرفي وداعم لمشاريع الجمعية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
