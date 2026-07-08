<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceDataIdsToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
            $table->unsignedBigInteger('designation_id')->nullable()->after('current_rank');
            $table->unsignedBigInteger('grade_id')->nullable()->after('grade');
            $table->unsignedBigInteger('step_id')->nullable()->after('step');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'designation_id', 'grade_id', 'step_id']);
        });
    }
}
