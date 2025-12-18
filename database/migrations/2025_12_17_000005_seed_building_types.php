<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $types = [
            ['name' => 'Maison', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Appartement', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Villa', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('building_types')->insert($types);
    }

    public function down(): void
    {
        DB::table('building_types')->whereIn('name', ['Maison', 'Appartement', 'Villa'])->delete();
    }
};
