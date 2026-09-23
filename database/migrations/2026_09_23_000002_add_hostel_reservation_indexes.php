<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        $exists = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$name]);
        if (!$exists) {
            Schema::table($table, function ($tableBlueprint) use ($columns, $name) {
                $tableBlueprint->index($columns, $name);
            });
        }
    }

    public function up(): void
    {
        if (!Schema::hasTable('hostel')) {
            return;
        }

        // Cover the student availability cascade and the atomic bed update
        // performed when many students submit at the same time.
        $this->addIndexIfMissing('hostel', ['flag', 'status', 'gender', 'bed_type', 'hall', 'block', 'room', 'bed'], 'hostel_availability_idx');
        $this->addIndexIfMissing('hostel', ['occupant'], 'hostel_occupant_idx');
        $this->addIndexIfMissing('hostel', ['hall', 'block', 'room', 'bed', 'status', 'bed_type'], 'hostel_bed_lookup_idx');

        if (Schema::hasTable('hostel_pin')) {
            $this->addIndexIfMissing('hostel_pin', ['username'], 'hostel_pin_username_idx');
        }

        if (Schema::hasTable('invoices')) {
            // Keep this compact because invoices.description is a long varchar.
            $this->addIndexIfMissing('invoices', ['username', 'session'], 'invoices_user_session_idx');
        }
    }

    public function down(): void
    {
        foreach ([
            ['hostel', 'hostel_availability_idx'],
            ['hostel', 'hostel_occupant_idx'],
            ['hostel', 'hostel_bed_lookup_idx'],
            ['hostel_pin', 'hostel_pin_username_idx'],
            ['invoices', 'invoices_user_session_idx'],
        ] as [$table, $index]) {
            if (Schema::hasTable($table)) {
                $exists = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);
                if ($exists) {
                    Schema::table($table, function ($tableBlueprint) use ($index) {
                        $tableBlueprint->dropIndex($index);
                    });
                }
            }
        }
    }
};
