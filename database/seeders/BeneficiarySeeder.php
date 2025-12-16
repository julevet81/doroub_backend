<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
            DB::table('beneficiaries')->insert([
    [
        'beneficiary_category_id' => 1,
        'full_name' => 'محمد علي',
        'date_of_birth' => '1980-05-12',
        'place_of_birth' => 'الجزائر العاصمة',
        'address' => 'حي الرياض',
        'phone_1' => '0550123456',
        'phone_2' => null,
        'social_status' => 'maried',
        'gender' => 'male',
        'nbr_in_family' => 5,
        'partner_id' => 1,
        'nbr_studing' => 2,
        'job' => 'عامل بناء',
        'insured' => true,
        'study_level' => 'primary',
        'health_status' => 'جيد',
        'income_source' => 'راتب',
        'barcode' => 'BEN-001',
        'national_id_file' => null,
        'municipality_id' => 1,
        'district_id' => 1,
        'city' => 'الجزائر',
        'neighborhood' => 'الرياض',
        'house_status' => 'owned',
        'national_id' => '1234567890123',
        'national_id_at' => '2020-01-01',
        'national_id_from' => 'الجزائر العاصمة',
        'father_name' => 'علي بن محمد',
        'mother_name' => 'فاطمة بنت سعيد',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'beneficiary_category_id' => 2,
        'full_name' => 'فاطمة الزهراء سعيد',
        'date_of_birth' => '1995-08-20',
        'place_of_birth' => 'وهران',
        'address' => 'حي الورد',
        'phone_1' => '0550987654',
        'phone_2' => '0550654321',
        'social_status' => 'single',
        'gender' => 'female',
        'nbr_in_family' => 4,
        'partner_id' => 2,
        'nbr_studing' => 1,
        'job' => 'معلمة',
        'insured' => false,
        'study_level' => 'secondary',
        'health_status' => 'جيد',
        'income_source' => 'عمل جزئي',
        'barcode' => 'BEN-002',
        'national_id_file' => null,
        'municipality_id' => 2,
        'district_id' => 2,
        'city' => 'وهران',
        'neighborhood' => 'الورد',
        'house_status' => 'rented',
        'national_id' => '9876543210987',
        'national_id_at' => '2021-05-10',
        'national_id_from' => 'وهران',
        'father_name' => 'سعيد بن أحمد',
        'mother_name' => 'ليلى بنت يوسف',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'beneficiary_category_id' => 1,
        'full_name' => 'علي الطاهر',
        'date_of_birth' => '1988-11-01',
        'place_of_birth' => 'قسنطينة',
        'address' => 'حي الزهور',
        'phone_1' => '0550333444',
        'phone_2' => null,
        'social_status' => 'divorced',
        'gender' => 'male',
        'nbr_in_family' => 3,
        'partner_id' => null,
        'nbr_studing' => 0,
        'job' => 'تاجر',
        'insured' => true,
        'study_level' => 'higher',
        'health_status' => 'متوسط',
        'income_source' => 'تجارة',
        'barcode' => 'BEN-003',
        'national_id_file' => null,
        'municipality_id' => null,
        'district_id' => null,
        'city' => 'قسنطينة',
        'neighborhood' => 'الزهور',
        'house_status' => 'family',
        'national_id' => '1122334455667',
        'national_id_at' => '2018-08-15',
        'national_id_from' => 'قسنطينة',
        'father_name' => 'الطاهر بن محمد',
        'mother_name' => 'حسنية بنت سعيد',
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

    }
}
