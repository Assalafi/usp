<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('results')) {
            return;
        }

        Schema::table('results', function (Blueprint $table): void {
            $table->index(
                ['session', 'semester', 'code', 'id'],
                'results_listing_session_idx'
            );
            $table->index(
                ['lecturer', 'session', 'code', 'semester', 'id'],
                'results_listing_lecturer_idx'
            );
            $table->index(
                ['program', 'department', 'session', 'semester', 'id'],
                'results_listing_program_idx'
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('results')) {
            return;
        }

        Schema::table('results', function (Blueprint $table): void {
            $table->dropIndex('results_listing_session_idx');
            $table->dropIndex('results_listing_lecturer_idx');
            $table->dropIndex('results_listing_program_idx');
        });
    }
};
