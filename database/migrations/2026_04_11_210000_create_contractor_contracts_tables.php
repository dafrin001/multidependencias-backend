<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Contratos de Contratistas
        Schema::create('contractor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained();
            $table->string('contract_number');
            $table->text('object')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_payment_value', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Reglas de Deducción (Estampillas, Retenciones)
        Schema::create('contract_deduction_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contractor_contracts')->onDelete('cascade');
            $table->string('name'); // Estampilla Pro-Cultura, ReteICA, etc.
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 15, 4); // 1.50 (1.5%) o 50000 (valor fijo)
            $table->boolean('is_recurrent')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_deduction_rules');
        Schema::dropIfExists('contractor_contracts');
    }
};
