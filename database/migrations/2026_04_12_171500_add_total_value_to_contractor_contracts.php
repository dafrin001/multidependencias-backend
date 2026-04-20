<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->decimal('total_contract_value', 20, 2)->after('end_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contractor_contracts', function (Blueprint $table) {
            $table->dropColumn('total_contract_value');
        });
    }
};
