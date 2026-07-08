<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('graduated_students', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->index();
            $table->string('fullname', 255)->nullable();
            $table->string('faculty', 50)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('program', 50)->nullable();
            $table->string('degree', 255)->nullable();
            $table->string('class_of_degree', 100);
            $table->string('graduation_date', 100)->nullable();
            $table->string('certificate_id', 100)->nullable()->unique();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('graduated_students');
    }
};
