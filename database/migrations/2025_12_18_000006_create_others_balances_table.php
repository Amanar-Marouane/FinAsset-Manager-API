<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('others_balances', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            // Unique constraint: one balance per account per month
            $table->unique(['year', 'month'], 'unique_balance_per_month');

            // Index for quick lookups
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('others_balances');
    }
};
