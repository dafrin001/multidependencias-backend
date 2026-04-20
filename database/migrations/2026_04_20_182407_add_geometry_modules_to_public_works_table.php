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
        Schema::table('public_works', function (Blueprint $table) {
            $table->string('geometry_type')->default('Point'); // Point, LineString, Polygon
            $table->longText('geometry_data')->nullable(); // GeoJSON coordinates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_works', function (Blueprint $table) {
            $table->dropColumn(['geometry_type', 'geometry_data']);
        });
    }
};
