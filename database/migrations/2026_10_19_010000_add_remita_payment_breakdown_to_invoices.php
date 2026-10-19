<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'base_amount')) {
                $table->decimal('base_amount', 15, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('invoices', 'surcharge_amount')) {
                $table->decimal('surcharge_amount', 15, 2)->default(0)->after('base_amount');
            }
            if (!Schema::hasColumn('invoices', 'split_enabled')) {
                $table->boolean('split_enabled')->default(false)->after('surcharge_amount');
            }
            if (!Schema::hasColumn('invoices', 'split_details')) {
                $table->text('split_details')->nullable()->after('split_enabled');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['split_details', 'split_enabled', 'surcharge_amount', 'base_amount'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
