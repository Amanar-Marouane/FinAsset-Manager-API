<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects') // references id on projects table
                ->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->timestamps();

            // Prevent duplicate entries for the same project/month/year
            $table->unique(['project_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_entries');
    }
};
