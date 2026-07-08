<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('siwes', function (Blueprint $table) {
            $table->id();
            // Username reference to students table (matric number)
            $table->string('username', 50);
            
            // SIWES-specific fields
            $table->date('period_of_attachment_from');
            $table->date('period_of_attachment_to');
            $table->text('placement_of_address');
            $table->string('bank_code', 20);
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('sort_code', 20)->nullable();
            $table->string('siwes_year', 20);
            $table->string('student_email_address', 100);
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->unique('username');
            $table->index('siwes_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siwes');
    }
};
