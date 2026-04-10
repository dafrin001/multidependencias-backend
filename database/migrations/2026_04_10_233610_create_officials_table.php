<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');                          // Nombre completo
            $table->string('document_number')->unique();         // Cédula / documento
            $table->string('document_type')->default('CC');      // CC, TI, CE, PS
            $table->string('position');                           // Cargo
            $table->foreignId('office_id')
                  ->constrained('offices')
                  ->onDelete('restrict');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('signature_url')->nullable();          // Ruta a la firma digitalizada
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
