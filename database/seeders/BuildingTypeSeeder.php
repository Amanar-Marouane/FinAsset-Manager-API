<?php

namespace Database\Seeders;

use App\Models\BuildingType;
use Illuminate\Database\Seeder;

class BuildingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Maison'],
            ['name' => 'Appartement'],
            ['name' => 'Villa'],
        ];

        foreach ($types as $type) {
            BuildingType::firstOrCreate($type);
        }
    }
}
