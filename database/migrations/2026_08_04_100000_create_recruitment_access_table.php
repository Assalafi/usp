<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentAccessTable extends Migration
{
    public function up()
    {
        Schema::create('recruitment_access', function (Blueprint $table) {
            $table->id();
            $table->string('username')->index();
            $table->string('name')->nullable();
            $table->boolean('can_access')->default(0);
            $table->boolean('can_export')->default(0);
            $table->boolean('can_view_cv')->default(0);
            $table->json('departments')->nullable();
            $table->json('posts')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recruitment_access');
    }
}
