<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('students', 'bank_code')) {
                $table->string('bank_code', 20)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('students', 'account_number')) {
                $table->string('account_number', 40)->nullable()->after('bank_code');
            }
            if (!Schema::hasColumn('students', 'sort_code')) {
                $table->string('sort_code', 40)->nullable()->after('account_number');
            }
        });

        // Keep bank information already captured by SIWES available in the
        // student's profile. This is deliberately limited to empty profile
        // fields so an edited profile remains the source of truth afterwards.
        if (Schema::hasTable('siwes')) {
            // The legacy tables use different MySQL collations. Match the
            // legacy username values as binary strings to avoid a collation
            // error during deployment.
            DB::table('siwes')
                ->whereNotNull('username')
                ->select(['username', 'bank_name', 'bank_code', 'account_number', 'sort_code'])
                ->orderBy('id')
                ->get()
                ->each(function ($siwes) {
                    $student = DB::table('students')
                        ->whereRaw('BINARY username = BINARY ?', [$siwes->username])
                        ->first(['id', 'bank_name', 'bank_code', 'account_number', 'sort_code']);

                    if (!$student) {
                        return;
                    }

                    $updates = [];
                    foreach (['bank_name', 'bank_code', 'account_number', 'sort_code'] as $field) {
                        if (empty($student->{$field}) && !empty($siwes->{$field})) {
                            $updates[$field] = $siwes->{$field};
                        }
                    }

                    if ($updates) {
                        DB::table('students')->where('id', $student->id)->update($updates);
                    }
                });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            foreach (['sort_code', 'account_number', 'bank_code', 'bank_name'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
