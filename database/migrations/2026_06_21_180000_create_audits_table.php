<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('acc_type')->nullable();
            $table->string('appointment')->nullable();
            $table->string('page');
            $table->string('url');
            $table->string('method', 10);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();

            $table->index('username');
            $table->index('page');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audits');
    }
};
