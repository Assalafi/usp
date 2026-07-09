<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPensionNumberToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'pension_number')) {
                $table->string('pension_number')->nullable()->after('bvn');
            }
        });

        // Copy existing BVN values to pension_number column
        DB::statement("UPDATE staff SET pension_number = bvn WHERE bvn IS NOT NULL AND bvn != ''");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'pension_number')) {
                $table->dropColumn('pension_number');
            }
        });
    }
}
