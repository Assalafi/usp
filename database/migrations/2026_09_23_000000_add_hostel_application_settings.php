<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the global hostel application controls used by the student portal.
     */
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        $settings = [
            [
                'key' => 'hostel_application_status',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'hostel',
                'label' => 'Hostel Application Status',
                'description' => 'Open or close new hostel bed-space reservations. Existing reservations remain accessible.',
            ],
            [
                'key' => 'hostel_closed_message',
                'value' => 'Hostel applications are currently closed. Please check back later or contact Student Affairs.',
                'type' => 'text',
                'category' => 'hostel',
                'label' => 'Closed Message',
                'description' => 'Message shown to students when new hostel reservations are closed.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_at' => now()])
            );
        }

        cache()->forget('system_settings');
    }

    public function down(): void
    {
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->whereIn('key', [
                'hostel_application_status',
                'hostel_closed_message',
            ])->delete();
        }
        cache()->forget('system_settings');
    }
};
