<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIdCardFeeSettings extends Migration
{
    /**
     * Insert ID Card fee settings without altering existing rows.
     */
    public function up()
    {
        $rows = [
            [
                'key' => 'id_card_fee',
                'value' => '2000',
                'category' => 'fees',
                'type' => 'number',
                'label' => 'ID Card Fee',
                'description' => 'Fee for student ID card generation',
            ],
            [
                'key' => 'id_card_service_type',
                'value' => '767553585',
                'category' => 'payment',
                'type' => 'text',
                'label' => 'ID Card Service Type ID',
                'description' => 'Remita service type for ID card payment',
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('system_settings')->where('key', $row['key'])->exists();
            if (!$exists) {
                DB::table('system_settings')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Clear settings cache so the new keys are picked up immediately
        if (function_exists('cache')) {
            cache()->forget('system_settings');
        }
        \Illuminate\Support\Facades\Cache::forget('system_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('system_settings')->whereIn('key', ['id_card_fee', 'id_card_service_type'])->delete();
        \Illuminate\Support\Facades\Cache::forget('system_settings');
    }
}
