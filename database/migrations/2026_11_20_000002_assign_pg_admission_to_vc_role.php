<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('link', '/pg-admission')->first();
        if (!$page || DB::table('rolls')->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])->where('link', '/pg-admission')->exists()) {
            return;
        }

        DB::table('rolls')->insert([
            'username' => 'VC',
            'faculty' => 'all',
            'department' => 'all',
            'program' => 'all',
            'level' => 'all',
            'main' => $page->main,
            'main_order' => $page->main_order,
            'page' => $page->page,
            'link' => $page->link,
            'sub_order' => $page->sub_order,
            'action' => $page->action ?: 'view',
            'status' => $page->status ?? '1',
            'user' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('rolls')
            ->whereRaw('LOWER(TRIM(username)) = ?', ['vc'])
            ->where('link', '/pg-admission')
            ->delete();
    }
};
