<?php

use Illuminate\Database\Migrations\Migration;
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
            ['key' => 'hostel_announcement'],
            [
                'value' => '',
                'category' => 'hostel',
                'type' => 'text',
                'label' => 'Hostel Announcement',
                'description' => 'Optional message displayed to students whether hostel applications are open or closed. Leave blank to hide it.',
                'updated_at' => now(),
            ]
        );

        cache()->forget('system_settings');
    }

    public function down(): void
    {
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->where('key', 'hostel_announcement')->delete();
        }

        cache()->forget('system_settings');
    }
};
