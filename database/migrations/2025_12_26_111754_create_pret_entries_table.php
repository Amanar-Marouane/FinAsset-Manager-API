<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pret_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pret_id')
                ->constrained('prets')
                ->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->timestamps();

            // Prevent duplicate entries for the same pret/month/year
            $table->unique(['pret_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pret_entries');
    }
};
