<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rufe_records', function (Blueprint $table) {
            $table->id();
            $table->string('departamento')->default('LA GUAJIRA');
            $table->string('municipio');
            $table->string('evento');
            $table->date('fecha_evento');
            $table->date('fecha_rufe');

            // Ubicación del bien
            $table->enum('ubicacion_tipo', ['URBANO', 'RURAL']);
            $table->string('corregimiento')->nullable();
            $table->string('vereda_sector_barrio')->nullable();
            $table->string('direccion')->nullable();

            // Forma de tenencia
            $table->enum('forma_tenencia', ['ARRENDATARIO', 'OCUPANTE', 'POSEEDOR', 'PROPIETARIO', 'NO INFORMA']);
            
            // Estado del bien
            $table->enum('estado_bien', ['HABITABLE', 'NO HABITABLE', 'DESTRUIDO', 'NO INFORMA', 'AVERIADO']);

            // Alojamiento actual
            $table->string('alojamiento_actual_tipo')->nullable(); // Lugar habitual / Evacuado fuera

            // Tipo de bien
            $table->string('tipo_bien')->nullable(); // Vivienda, Finca, etc.

            $table->text('observaciones')->nullable();
            $table->string('vo_bo')->nullable();

            $table->timestamps();
        });

        Schema::create('rufe_demographics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rufe_record_id')->constrained('rufe_records')->onDelete('cascade');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('tipo_documento'); // 1-10 mapping
            $table->string('numero_documento');
            $table->string('parentesco'); // 1-15 mapping
            $table->enum('genero', ['MASCULINO', 'FEMENINO', 'TRANSGENERO']);
            $table->date('fecha_nacimiento');
            $table->string('pertenencia_etnica'); // 1-6 mapping
            $table->string('telefono')->nullable();
            $table->timestamps();
        });

        Schema::create('rufe_agropecuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rufe_record_id')->constrained('rufe_records')->onDelete('cascade');
            $table->string('tipo_cultivo')->nullable();
            $table->string('unidad_medida')->nullable();
            $table->string('area_cantidad')->nullable();
            $table->string('sector_pecuario_especie')->nullable();
            $table->string('cantidad_unidades')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rufe_agropecuarios');
        Schema::dropIfExists('rufe_demographics');
        Schema::dropIfExists('rufe_records');
    }
};
