<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_id')
                ->constrained('credits')
                ->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->timestamps();

            // Prevent duplicate entries for the same credit/month/year
            $table->unique(['credit_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_entries');
    }
};
