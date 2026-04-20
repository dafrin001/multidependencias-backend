<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->string('contract_type')->default('ops')->after('contract_number'); // ops, interadministrativo, etc.
        });
    }

    public function down(): void
    {
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->dropColumn('contract_type');
        });
    }
};
