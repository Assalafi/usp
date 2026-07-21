<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocTradeTestToStaffTable extends Migration
{
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'doc_trade_test')) {
                $table->string('doc_trade_test')->nullable()->after('doc_nysc');
            }
        });
    }

    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'doc_trade_test')) {
                $table->dropColumn('doc_trade_test');
            }
        });
    }
}
