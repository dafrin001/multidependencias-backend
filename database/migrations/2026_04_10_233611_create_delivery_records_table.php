<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_records', function (Blueprint $table) {
            $table->id();
            $table->string('acta_number')->unique();              // Número de acta (auto-generado)
            $table->enum('type', ['asset', 'consumable']);        // Tipo de entrega

            // Qué se entrega — uno de los dos:
            $table->foreignId('fixed_asset_id')
                  ->nullable()
                  ->constrained('fixed_assets')
                  ->onDelete('restrict');
            $table->foreignId('item_id')                         // Para consumibles
                  ->nullable()
                  ->constrained('items')
                  ->onDelete('restrict');
            $table->unsignedInteger('quantity')->default(1);      // Cant. entregada (consumibles)

            // A quién se entrega:
            $table->foreignId('official_id')
                  ->constrained('officials')
                  ->onDelete('restrict');

            // Quién entrega:
            $table->string('delivered_by');                       // Nombre del funcionario que entrega

            $table->date('delivery_date');
            $table->text('notes')->nullable();                    // Observaciones

            // Firma digital del receptor (Base64 o URL del archivo)
            $table->longText('signature_data')->nullable();

            // Devolución
            $table->boolean('is_returned')->default(false);
            $table->date('returned_date')->nullable();
            $table->text('return_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_records');
    }
};
