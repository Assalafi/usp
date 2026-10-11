<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff', 'blood_group')) {
            Schema::table('staff', function (Blueprint $table): void {
                $table->string('blood_group', 10)->nullable()->after('nationality');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('staff', 'blood_group')) {
            Schema::table('staff', function (Blueprint $table): void {
                $table->dropColumn('blood_group');
            });
        }
    }
};
