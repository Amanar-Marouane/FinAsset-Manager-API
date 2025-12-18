<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $banks = [
            ['name' => 'CIH', 'description' => 'Crédit Immobilier et Hôtelier', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BMCE', 'description' => 'Banque Marocaine du Commerce Extérieur', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WAFAE BANK', 'description' => 'Wafabank', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('banks')->insert($banks);
    }

    public function down(): void
    {
        DB::table('banks')->whereIn('name', ['CIH', 'BMCE', 'WAFAE BANK'])->delete();
    }
};
