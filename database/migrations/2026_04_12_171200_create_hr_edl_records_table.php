<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_edl_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained();
            $table->integer('year');
            $table->enum('period', ['semestral_1', 'semestral_2', 'anual'])->default('anual');
            $table->integer('score')->nullable(); // 0-100
            $table->text('compromises')->nullable(); // Compromisos acordados
            $table->text('feedback')->nullable();
            $table->enum('status', ['draft', 'agreed', 'evaluated', 'closed'])->default('draft');
            $table->string('file_url')->nullable(); // Acta de calificación firmada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_edl_records');
    }
};
