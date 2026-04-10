<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Solo aplica a consumibles (is_asset = false)
            $table->unsignedInteger('stock')->default(0)->after('is_asset')
                  ->comment('Unidades disponibles en bodega');
            $table->unsignedInteger('min_stock')->default(5)->after('stock')
                  ->comment('Cantidad mínima antes de generar alerta');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['stock', 'min_stock']);
        });
    }
};
