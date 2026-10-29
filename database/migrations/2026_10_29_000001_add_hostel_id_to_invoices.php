<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices') || Schema::hasColumn('invoices', 'hostel_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            // Keep this nullable so existing invoices remain valid. The value
            // identifies the exact hostel row paid by a new hostel invoice.
            $table->unsignedBigInteger('hostel_id')->nullable()->after('username');
            $table->index('hostel_id', 'invoices_hostel_id_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'hostel_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_hostel_id_idx');
            $table->dropColumn('hostel_id');
        });
    }
};
