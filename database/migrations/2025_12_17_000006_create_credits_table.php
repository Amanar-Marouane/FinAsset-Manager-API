<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            // montant (net, with interest)
            $table->decimal('montant', 15, 2);
            // monthly payment (installment)
            $table->decimal('monthly_payment', 15, 2)->nullable();
            // organization: where the credit comes from
            $table->string('organization')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
