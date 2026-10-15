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

        $settings = [
            [
                'key' => 'hod_program_course_management_mode',
                'value' => 'view',
                'type' => 'select',
                'category' => 'academic',
                'label' => 'Programme course access for Heads of Department',
                'description' => 'Choose whether Heads of Department may only review programme courses or may also maintain them.',
            ],
            [
                'key' => 'hod_program_course_management_users',
                'value' => '[]',
                'type' => 'json',
                'category' => 'academic',
                'label' => 'Selected Heads of Department',
                'description' => 'Used when selected Heads of Department are allowed to maintain programme courses.',
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
                'hod_program_course_management_mode',
                'hod_program_course_management_users',
            ])->delete();
        }

        cache()->forget('system_settings');
    }
};
