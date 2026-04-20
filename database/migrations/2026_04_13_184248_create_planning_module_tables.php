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
        Schema::create('public_works', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('status', ['pending', 'started', 'in_progress', 'completed', 'delivered'])->default('pending');
            $table->string('image_url')->nullable();
            $table->integer('beneficiaries_count')->default(0);
            $table->decimal('budget', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('land_use_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // agricultural, residential, etc.
            $table->decimal('area_km2', 12, 2)->default(0);
            $table->text('geometry_json'); // To store GeoJSON or coordinates
            $table->text('description')->nullable();
            $table->decimal('valuation', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_use_sectors');
        Schema::dropIfExists('public_works');
    }
};
