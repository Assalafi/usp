<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!Schema::hasColumn('students', 'nin_update_version')) {
                    $table->unsignedInteger('nin_update_version')->default(0)->index();
                }
                if (!Schema::hasColumn('students', 'nin_updated_at')) {
                    $table->timestamp('nin_updated_at')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'student_nin_update_version'],
                [
                    'value' => '1',
                    'category' => 'security',
                    'type' => 'number',
                    'label' => 'Student NIN confirmation cycle',
                    'description' => 'Students must confirm their NIN whenever this cycle is increased. Use the request button to start a new confirmation cycle.',
                    'updated_at' => now(),
                ]
            );
        }

        Cache::forget('system_settings');
    }

    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                foreach (['nin_updated_at', 'nin_update_version'] as $column) {
                    if (Schema::hasColumn('students', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->where('key', 'student_nin_update_version')->delete();
        }

        Cache::forget('system_settings');
    }
};
