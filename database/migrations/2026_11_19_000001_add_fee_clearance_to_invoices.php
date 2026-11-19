<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'fee_clearance')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->boolean('fee_clearance')->default(false)->after('description');
                $table->index(['username', 'fee_clearance', 'status'], 'invoices_fee_clearance_lookup');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'fee_clearance')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('invoices_fee_clearance_lookup');
                $table->dropColumn('fee_clearance');
            });
        }
    }
};
