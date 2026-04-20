<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de ítems del acta
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_record_id')
                  ->constrained('delivery_records')
                  ->cascadeOnDelete();

            $table->enum('type', ['asset', 'consumable']);

            $table->foreignId('fixed_asset_id')
                  ->nullable()
                  ->constrained('fixed_assets')
                  ->nullOnDelete();

            $table->foreignId('item_id')
                  ->nullable()
                  ->constrained('items')
                  ->nullOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // Descripción textual del ítem (snapshot al momento de la entrega)
            $table->string('description')->nullable();

            $table->timestamps();
        });

        // 2. Migrar datos históricos: cada delivery_record con un ítem se convierte en un delivery_item
        $records = DB::table('delivery_records')
            ->whereNull('deleted_at')
            ->get(['id', 'type', 'fixed_asset_id', 'item_id', 'quantity']);

        foreach ($records as $record) {
            DB::table('delivery_items')->insert([
                'delivery_record_id' => $record->id,
                'type'               => $record->type,
                'fixed_asset_id'     => $record->fixed_asset_id,
                'item_id'            => $record->item_id,
                'quantity'           => $record->quantity ?? 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // 3. Hacer nullable las columnas legacy en delivery_records (no se eliminan para compatibilidad)
        Schema::table('delivery_records', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->unsignedInteger('quantity')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');

        Schema::table('delivery_records', function (Blueprint $table) {
            $table->enum('type', ['asset', 'consumable'])->nullable(false)->change();
            $table->unsignedInteger('quantity')->default(1)->nullable(false)->change();
        });
    }
};
