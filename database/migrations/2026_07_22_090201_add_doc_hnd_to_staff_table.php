<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocHndToStaffTable extends Migration
{
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'doc_hnd')) {
                $table->string('doc_hnd')->nullable()->after('doc_diploma');
            }
        });
    }

    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'doc_hnd')) {
                $table->dropColumn('doc_hnd');
            }
        });
    }
}
