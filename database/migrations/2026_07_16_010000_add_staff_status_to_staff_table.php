<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffStatusToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'staff_status')) {
                $table->string('staff_status')->default('Active')->after('current_qualification');
            }
            if (!Schema::hasColumn('staff', 'leave_institution')) {
                $table->string('leave_institution')->nullable()->after('staff_status');
            }
            if (!Schema::hasColumn('staff', 'leave_start_date')) {
                $table->date('leave_start_date')->nullable()->after('leave_institution');
            }
            if (!Schema::hasColumn('staff', 'leave_end_date')) {
                $table->date('leave_end_date')->nullable()->after('leave_start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'staff_status')) {
                $table->dropColumn('staff_status');
            }
            if (Schema::hasColumn('staff', 'leave_institution')) {
                $table->dropColumn('leave_institution');
            }
            if (Schema::hasColumn('staff', 'leave_start_date')) {
                $table->dropColumn('leave_start_date');
            }
            if (Schema::hasColumn('staff', 'leave_end_date')) {
                $table->dropColumn('leave_end_date');
            }
        });
    }
}
