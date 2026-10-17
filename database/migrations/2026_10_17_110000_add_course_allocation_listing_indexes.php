<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_allocation')) {
            return;
        }

        Schema::table('course_allocation', function (Blueprint $table): void {
            $table->index(
                ['username', 'session', 'course', 'type'],
                'course_allocation_staff_session_idx'
            );
            $table->index(
                ['department', 'session', 'course', 'username'],
                'course_allocation_department_session_idx'
            );
            $table->index(
                ['session', 'course', 'type', 'username'],
                'course_allocation_course_session_idx'
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('course_allocation')) {
            return;
        }

        Schema::table('course_allocation', function (Blueprint $table): void {
            $table->dropIndex('course_allocation_staff_session_idx');
            $table->dropIndex('course_allocation_department_session_idx');
            $table->dropIndex('course_allocation_course_session_idx');
        });
    }
};
