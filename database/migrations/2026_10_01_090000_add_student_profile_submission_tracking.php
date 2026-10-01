<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'profile_status')) {
                $table->string('profile_status', 30)->default('draft')->index();
            }
            if (!Schema::hasColumn('students', 'profile_submitted_at')) {
                $table->timestamp('profile_submitted_at')->nullable()->index();
            }
            if (!Schema::hasColumn('students', 'profile_last_updated_at')) {
                $table->timestamp('profile_last_updated_at')->nullable()->index();
            }
            if (!Schema::hasColumn('students', 'profile_submission_session')) {
                $table->string('profile_submission_session', 30)->nullable();
            }
            if (!Schema::hasColumn('students', 'profile_level_session')) {
                $table->string('profile_level_session', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            foreach ([
                'profile_level_session',
                'profile_submission_session',
                'profile_last_updated_at',
                'profile_submitted_at',
                'profile_status',
            ] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
