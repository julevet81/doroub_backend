<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            DistrictSeeder::class,
            MunicipalitySeeder::class,
            ProjectSeeder::class,
            DeviceSeeder::class,
            AssistanceItemSeeder::class,
            VolunteerSeeder::class,
            AssistanceCategorySeeder::class,
            //BeneficiarySeeder::class,
            DonorSeeder::class,
        ]);
    }
}
