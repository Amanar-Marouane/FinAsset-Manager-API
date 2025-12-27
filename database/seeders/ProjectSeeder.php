<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Solar Expansion',
                'capital' => '750000.00',
                'entries' => [
                    ['amount' => '45000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '52000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '61000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
            [
                'name' => 'Logistics Upgrade',
                'capital' => '420000.00',
                'entries' => [
                    ['amount' => '38000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '41000.00', 'month' => 4, 'year' => 2025],
                    ['amount' => '39000.00', 'month' => 5, 'year' => 2025],
                ],
            ],
            [
                'name' => 'Retail Expansion',
                'capital' => '300000.00',
                'entries' => [
                    ['amount' => '25000.00', 'month' => 9, 'year' => 2025],
                    ['amount' => '27000.00', 'month' => 10, 'year' => 2025],
                    ['amount' => '30000.00', 'month' => 12, 'year' => 2025],
                ],
            ],
        ];

        foreach ($projects as $data) {
            $entries = $data['entries'];
            unset($data['entries']);

            $project = Project::create($data);
            $project->entries()->createMany($entries);
        }
    }
}
