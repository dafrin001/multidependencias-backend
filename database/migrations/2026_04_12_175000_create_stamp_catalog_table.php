<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('default_value', 10, 4);
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->timestamps();
        });

        DB::table('stamp_catalog')->insert([
            ['name' => 'Estampilla Pro-Cultura', 'default_value' => 1.5, 'type' => 'percentage'],
            ['name' => 'Estampilla Adulto Mayor', 'default_value' => 2.0, 'type' => 'percentage'],
            ['name' => 'ReteICA', 'default_value' => 0.7, 'type' => 'percentage'],
            ['name' => 'Pro-Deporte', 'default_value' => 1.0, 'type' => 'percentage'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_catalog');
    }
};
