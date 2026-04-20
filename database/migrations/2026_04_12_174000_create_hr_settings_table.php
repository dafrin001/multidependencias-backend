<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('value');
            $table->string('type')->default('percentage'); // percentage, fixed, string
            $table->timestamps();
        });

        // Valores por defecto
        DB::table('hr_settings')->insert([
            ['key' => 'health_deduction_rate', 'label' => 'Descuento Salud (Planta)', 'value' => '4.0', 'type' => 'percentage'],
            ['key' => 'pension_deduction_rate', 'label' => 'Descuento Pensión (Planta)', 'value' => '4.0', 'type' => 'percentage'],
            ['key' => 'arl_rate', 'label' => 'Tasa ARL Promedio', 'value' => '0.522', 'type' => 'percentage'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};
