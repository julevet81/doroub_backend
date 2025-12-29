<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List all your permissions
        $permissions = [
            'إضافة مستخدمين',
            'تعديل مستخدم',
            'حذف مستخدم',
            'عرض مستخدم',
            'ادارة المستخدمين',

            'أدارة الأدوار',
            'إضافة أدوار',

            'عرض المستفيدين',
            'إحصائيات المستفيدين',
            'عرض الإستفادات',
            'عرض المشاريع',
            'عرض الأجهزة',
            'عرض المصاريف',
            'عرض المالية',
            'الداخل للمخزون',
            'الخارج من المخزون',
            'عناصر المخزون',
            'الإعارات',
            'الطلبات',
            'التسجيلات',
            'المتبرعين',
            'عرض المتطوعين',
            'اجصائيات المتطوعين',
            'المداخيل',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }
    }
}
