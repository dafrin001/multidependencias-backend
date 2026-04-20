<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cargos y Manual de Funciones
        Schema::create('positions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('code')->nullable(); // Código DAFP
            $blueprint->string('grade')->nullable(); // Grado salarial
            $blueprint->decimal('base_salary', 15, 2);
            $blueprint->text('functions')->nullable(); // Manual de funciones
            $blueprint->enum('level', ['directivo', 'asesor', 'profesional', 'tecnico', 'asistencial']);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        // 2. Ampliación de Funcionarios para TH
        Schema::table('officials', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->constrained('positions');
            $table->enum('employment_type', ['carrera', 'provisional', 'libre_nombramiento', 'contratista'])->default('provisional');
            $table->enum('employment_status', ['activo', 'licencia', 'comision', 'encargo', 'suspendido', 'retirado'])->default('activo');
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('retirement_reason')->nullable();
            $table->boolean('sigep_updated')->default(false);
        });

        // 3. Nóminas y Compensaciones
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'processed', 'paid', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained();
            $table->foreignId('official_id')->constrained();
            $table->decimal('salary_base', 15, 2);
            $table->decimal('allowances', 15, 2)->default(0); // Primas, bonos
            $table->decimal('overtime', 15, 2)->default(0);
            $table->decimal('deductions_health', 15, 2)->default(0);
            $table->decimal('deductions_pension', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2);
            $table->json('details')->nullable(); // Desglose de liquidación
            $table->timestamps();
        });

        // 4. Bienestar y Capacitación (PIC)
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['tecnica', 'profesional', 'induccion', 'bienestar']);
            $table->date('scheduled_date');
            $table->integer('hours')->default(0);
            $table->integer('max_attendees')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->timestamps();
        });

        Schema::create('training_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained();
            $table->foreignId('official_id')->constrained();
            $table->boolean('attended')->default(false);
            $table->integer('score')->nullable();
            $table->string('certificate_url')->nullable();
            $table->timestamps();
        });

        // 5. Seguridad y Salud en el Trabajo (SST)
        Schema::create('sst_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained();
            $table->enum('type', ['examen_ingreso', 'examen_periodico', 'examen_egreso', 'incidente', 'accidente']);
            $table->date('record_date');
            $table->string('provider_name')->nullable();
            $table->text('findings')->nullable();
            $table->string('file_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sst_records');
        Schema::dropIfExists('training_attendees');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
        Schema::table('officials', function (Blueprint $table) {
            $table->dropColumn(['position_id', 'employment_type', 'employment_status', 'entry_date', 'exit_date', 'retirement_reason', 'sigep_updated']);
        });
        Schema::dropIfExists('positions');
    }
};
