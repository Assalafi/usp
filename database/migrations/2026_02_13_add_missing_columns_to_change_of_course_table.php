<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('change_of_course', function (Blueprint $table) {
            $table->string('new_dean_name')->nullable()->after('new_dean_recommendation');
            $table->string('current_dean_name')->nullable()->after('current_dean_recommendation');
            $table->string('registrar_name')->nullable()->after('registrar_decision');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('change_of_course', function (Blueprint $table) {
            $table->dropColumn(['new_dean_name', 'current_dean_name', 'registrar_name']);
        });
    }
};
