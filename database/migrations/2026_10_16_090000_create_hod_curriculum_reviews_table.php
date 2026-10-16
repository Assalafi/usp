<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hod_curriculum_reviews')) {
            return;
        }

        Schema::create('hod_curriculum_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('hod_username', 100);
            $table->string('department', 30);
            $table->unsignedBigInteger('structure_id');
            $table->string('status', 20)->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_by', 100)->nullable();
            $table->timestamp('last_changed_at')->nullable();
            $table->string('last_changed_by', 100)->nullable();
            $table->string('last_change_action', 100)->nullable();
            $table->timestamps();

            $table->unique(['hod_username', 'structure_id'], 'hod_curriculum_review_unique');
            $table->index(['department', 'structure_id'], 'hod_curriculum_review_department_structure');
            $table->index(['status', 'structure_id'], 'hod_curriculum_review_status_structure');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hod_curriculum_reviews');
    }
};
