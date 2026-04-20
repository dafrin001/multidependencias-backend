<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Comités de Ley
        Schema::create('hr_committees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // COPASST, Comité de Convivencia, etc.
            $table->text('description')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Miembros de Comités
        Schema::create('hr_committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_committee_id')->constrained()->onDelete('cascade');
            $table->foreignId('official_id')->constrained();
            $table->string('role')->default('Principal'); // Presidente, Secretario, Suplente, etc.
            $table->date('appointment_date')->nullable();
            $table->timestamps();
        });

        // 3. Actas y Reuniones
        Schema::create('hr_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_committee_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->date('meeting_date');
            $table->time('meeting_time')->nullable();
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->longText('minutes_content')->nullable(); // Contenido del acta
            $table->enum('status', ['scheduled', 'held', 'cancelled'])->default('scheduled');
            $table->string('file_url')->nullable(); // PDF del acta firmada
            $table->timestamps();
        });

        // 4. Situaciones Administrativas (Ausentismo)
        Schema::create('hr_administrative_situations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained();
            $table->enum('type', ['vacaciones', 'permiso', 'licencia_maternidad', 'licencia_paternidad', 'licencia_luto', 'incapacidad', 'comision', 'encargo', 'otro']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('document_url')->nullable();
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado', 'completado'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_administrative_situations');
        Schema::dropIfExists('hr_meetings');
        Schema::dropIfExists('hr_committee_members');
        Schema::dropIfExists('hr_committees');
    }
};
