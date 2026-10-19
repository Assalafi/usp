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

        $postUtme = DB::table('system_settings')->where('key', 'remita_putme_service_type')->value('value')
            ?: (string) env('REMITA_POST_UTME_KEY', '4430731');
        $idCard = DB::table('system_settings')->where('key', 'id_card_service_type')->value('value')
            ?: '767553585';

        $catalog = [
            [
                'key' => 'school_fees',
                'label' => 'School fees',
                'service_type_id' => (string) env('REMITA_SCHOOL_FEES_KEY', '365039916'),
                'description' => 'Student school-fee invoices.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'hostel',
                'label' => 'Hostel accommodation',
                'service_type_id' => (string) env('REMITA_HOSTEL_KEY', ''),
                'description' => 'Hostel reservation invoices.',
                'active' => (string) env('REMITA_HOSTEL_KEY', '') !== '',
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'post_utme',
                'label' => 'Post-UTME',
                'service_type_id' => (string) $postUtme,
                'description' => 'Post-UTME application payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'id_card',
                'label' => 'Student ID card',
                'service_type_id' => (string) $idCard,
                'description' => 'Student identity-card payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'change_of_course',
                'label' => 'Change of course',
                'service_type_id' => (string) env('REMITA_CHANGE_OF_COURSE_KEY', '4430731'),
                'description' => 'Change-of-course application payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'inter_university_transfer',
                'label' => 'Inter-university transfer',
                'service_type_id' => (string) env('REMITA_INTER_TRANSFER_KEY', '4430731'),
                'description' => 'Inter-university transfer payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
        ];

        $settings = [
            [
                'key' => 'remita_service_catalog',
                'value' => json_encode($catalog, JSON_UNESCAPED_SLASHES),
                'type' => 'json',
                'category' => 'payment',
                'label' => 'Remita service types',
                'description' => 'Central registry of every Remita service type used by the portal. Manage IDs and split eligibility from Payment Gateway settings.',
            ],
            [
                'key' => 'remita_split_settings',
                'value' => json_encode([
                    'enabled' => false,
                    'surcharge' => '0.00',
                    'fee_bearer' => 'primary',
                    'beneficiaries' => [
                        ['key' => 'primary', 'label' => 'University main account', 'account_number' => '', 'bank_code' => '', 'percentage' => 40],
                        ['key' => 'additional_1', 'label' => 'Additional account 1', 'account_number' => '', 'bank_code' => '', 'percentage' => 30],
                        ['key' => 'additional_2', 'label' => 'Additional account 2', 'account_number' => '', 'bank_code' => '', 'percentage' => 30],
                    ],
                ], JSON_UNESCAPED_SLASHES),
                'type' => 'json',
                'category' => 'payment',
                'label' => 'Remita split-payment settings',
                'description' => 'Controls the optional additional charge and beneficiary allocation used by selected services.',
            ],
        ];

        foreach ($settings as $setting) {
            if (!DB::table('system_settings')->where('key', $setting['key'])->exists()) {
                DB::table('system_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        cache()->forget('system_settings');
    }

    public function down(): void
    {
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->whereIn('key', [
                'remita_service_catalog',
                'remita_split_settings',
            ])->delete();
        }

        cache()->forget('system_settings');
    }
};
