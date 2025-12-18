<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')->insert([
            'id' => Str::uuid()->toString(),
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('passwordmanager123'),
            'refresh_token_hash' => null,
            'expired_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'manager@gmail.com')->delete();
    }
};
