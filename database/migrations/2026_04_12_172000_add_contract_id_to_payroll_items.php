<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->foreignId('contractor_contract_id')->nullable()->constrained('contractor_contracts')->after('official_id');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropForeign(['contractor_contract_id']);
            $table->dropColumn('contractor_contract_id');
        });
    }
};
