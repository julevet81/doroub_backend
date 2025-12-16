<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('devices')->insert([
            [
                'name' => 'جهاز قياس الحرارة',
                'serial_number' => 'SN-1001',
                'usage_count' => 10,
                'status' => true,
                'is_destructed' => false,
                'destruction_report' => null,
                'destruction_reason' => null,
                'barcode' => 'BC-1001',
                'is_new' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جهاز قياس الضغط',
                'serial_number' => 'SN-1002',
                'usage_count' => 5,
                'status' => true,
                'is_destructed' => false,
                'destruction_report' => null,
                'destruction_reason' => null,
                'barcode' => 'BC-1002',
                'is_new' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جهاز مراقبة السكر',
                'serial_number' => 'SN-1003',
                'usage_count' => 0,
                'status' => false,
                'is_destructed' => false,
                'destruction_report' => null,
                'destruction_reason' => null,
                'barcode' => 'BC-1003',
                'is_new' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جهاز الأشعة المحمولة',
                'serial_number' => 'SN-1004',
                'usage_count' => 2,
                'status' => true,
                'is_destructed' => false,
                'destruction_report' => null,
                'destruction_reason' => null,
                'barcode' => 'BC-1004',
                'is_new' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
