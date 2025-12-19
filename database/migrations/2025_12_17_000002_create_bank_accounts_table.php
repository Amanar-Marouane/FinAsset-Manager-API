<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')
                ->constrained('banks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('account_name')->default('');
            $table->string('account_number')->default('');
            $table->string('currency')->default('MAD');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
