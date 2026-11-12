<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('navigation_actions')) {
            Schema::create('navigation_actions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->string('label', 120);
                $table->string('created_by', 120)->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        foreach (['view', 'create', 'update', 'delete', 'upload', 'approve', 'export'] as $action) {
            DB::table('navigation_actions')->updateOrInsert(
                ['name' => $action],
                ['label' => ucfirst($action), 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_actions');
    }
};
