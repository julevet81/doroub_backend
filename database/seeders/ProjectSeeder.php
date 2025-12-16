<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Relief Aid for Flood Victims',
                'type' => 'relief',
                'budget' => 50000.00,
                'start_date' => '2025-01-10',
                'end_date' => '2025-03-10',
                'status' => 'completed',
                'location' => 'Algiers',
                'description' => 'Providing emergency relief supplies for flood-affected areas.',
            ],
            [
                'name' => 'Community Solidarity Campaign',
                'type' => 'solidarity',
                'budget' => 20000.00,
                'start_date' => '2025-02-01',
                'end_date' => '2025-04-01',
                'status' => 'in_progress',
                'location' => 'Oran',
                'description' => 'Organizing community events to strengthen social bonds.',
            ],
            [
                'name' => 'Health Awareness Program',
                'type' => 'healthyh',
                'budget' => 15000.00,
                'start_date' => '2025-03-01',
                'end_date' => '2025-06-01',
                'status' => 'planned',
                'location' => 'Constantine',
                'description' => 'Educating the public about healthy lifestyle choices.',
            ],
            [
                'name' => 'Educational Support for Children',
                'type' => 'educational',
                'budget' => 30000.00,
                'start_date' => '2025-04-01',
                'end_date' => null,
                'status' => 'planned',
                'location' => 'Annaba',
                'description' => 'Providing educational materials and support to children in need.',
            ],
            [
                'name' => 'Entertainment for Orphanages',
                'type' => 'entertainment',
                'budget' => 10000.00,
                'start_date' => '2025-05-01',
                'end_date' => '2025-05-15',
                'status' => 'planned',
                'location' => 'Tlemcen',
                'description' => 'Organizing fun events and activities for orphans.',
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
