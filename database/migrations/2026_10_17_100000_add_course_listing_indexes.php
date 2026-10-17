<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course')) {
            return;
        }

        Schema::table('course', function (Blueprint $table): void {
            $table->index(
                ['faculty', 'department', 'program', 'status', 'code'],
                'course_catalogue_entity_idx'
            );
            $table->index(
                ['level', 'semester', 'type', 'class', 'unit', 'status', 'code'],
                'course_catalogue_structure_idx'
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('course')) {
            return;
        }

        Schema::table('course', function (Blueprint $table): void {
            $table->dropIndex('course_catalogue_entity_idx');
            $table->dropIndex('course_catalogue_structure_idx');
        });
    }
};
