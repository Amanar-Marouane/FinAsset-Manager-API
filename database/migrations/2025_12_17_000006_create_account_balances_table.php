<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')
                ->constrained('bank_accounts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            // Unique constraint: one balance per account per month
            $table->unique(['bank_account_id', 'year', 'month'], 'unique_balance_per_month');

            // Index for quick lookups
            $table->index(['bank_account_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
