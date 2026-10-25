<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'student_nin_verification_enabled'],
            [
                'value' => '1',
                'category' => 'security',
                'type' => 'boolean',
                'label' => 'Student NIN verification',
                'description' => 'Require undergraduate students to confirm the NIN already stored on their record before accessing portal services.',
                'updated_at' => now(),
            ]
        );

        Cache::forget('system_settings');
    }

    public function down(): void
    {
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')
                ->where('key', 'student_nin_verification_enabled')
                ->delete();
        }

        Cache::forget('system_settings');
    }
};
