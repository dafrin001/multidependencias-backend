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
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->string('cdp')->nullable()->after('contract_number');
            $table->string('rp')->nullable()->after('cdp');
            $table->string('rubro')->nullable()->after('rp');
            $table->string('supervisor_name')->nullable()->after('object');
            $table->string('supervisor_position')->nullable()->after('supervisor_name');
            $table->integer('arl_risk_level')->default(1)->after('supervisor_position');
            $table->text('observations')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->dropColumn(['cdp', 'rp', 'rubro', 'supervisor_name', 'supervisor_position', 'arl_risk_level', 'observations']);
        });
    }
};
