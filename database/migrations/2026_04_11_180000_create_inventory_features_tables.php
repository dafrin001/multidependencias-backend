<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Entradas de Inventario (recepción de bienes) ─────────────
        Schema::create('inventory_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();          // ENTRADA-2026-0001
            $table->foreignId('supplier_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->string('invoice_number')->nullable();      // Número de factura/orden
            $table->date('entry_date');
            $table->string('received_by');                     // Quien recibe
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();
        });

        Schema::create('inventory_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── 2. Actas de Baja de Activos ─────────────────────────────────
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->string('disposal_number')->unique();       // BAJA-2026-0001
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->enum('reason', [
                'damage', 'loss', 'obsolescence', 'theft', 'transfer', 'other'
            ]);
            $table->date('disposal_date');
            $table->string('authorized_by');                   // Quien autoriza la baja
            $table->string('processed_by');                    // Quien tramita
            $table->text('description')->nullable();
            $table->string('resolution_number')->nullable();   // Número de resolución administrativa
            $table->timestamps();
        });

        // ── 3. Transferencias entre Dependencias ────────────────────────
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();       // TRASL-2026-0001
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('to_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('transferred_by');
            $table->string('received_by')->nullable();
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();
        });

        // ── 4. Mantenimiento de Activos ──────────────────────────────────
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['preventive', 'corrective', 'upgrade']);
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->string('technician')->nullable();          // Técnico / empresa
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('description');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();
        });

        // ── 5. Solicitudes de Suministros ───────────────────────────────
        Schema::create('supply_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();        // SOL-2026-0001
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('requested_by');
            $table->date('request_date');
            $table->date('needed_by')->nullable();             // Fecha límite requerida
            $table->enum('status', ['pending', 'approved', 'dispatched', 'rejected', 'partial'])->default('pending');
            $table->date('dispatch_date')->nullable();
            $table->string('dispatched_by')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('supply_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->nullable();
            $table->integer('dispatched_quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── 6. Campos adicionales en activos fijos ───────────────────────
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->date('warranty_expiry_date')->nullable()->after('purchase_price');
            $table->decimal('depreciation_rate', 5, 2)->nullable()->after('warranty_expiry_date'); // % anual
            $table->boolean('is_disposed')->default(false)->after('depreciation_rate');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn(['warranty_expiry_date', 'depreciation_rate', 'condition', 'is_disposed']);
        });
        Schema::dropIfExists('supply_request_items');
        Schema::dropIfExists('supply_requests');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('inventory_entry_items');
        Schema::dropIfExists('inventory_entries');
    }
};
