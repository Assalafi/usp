<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('program_course_registration') || Schema::hasColumn('program_course_registration', 'prerequisites')) {
            return;
        }

        Schema::table('program_course_registration', function (Blueprint $table) {
            // Comma/new-line separated course codes. Keeping this text based preserves
            // compatibility with existing MySQL installations and old curriculum rows.
            $table->text('prerequisites')->nullable()->after('grading');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('program_course_registration') && Schema::hasColumn('program_course_registration', 'prerequisites')) {
            Schema::table('program_course_registration', function (Blueprint $table) {
                $table->dropColumn('prerequisites');
            });
        }
    }
};
