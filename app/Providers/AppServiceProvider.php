<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share the semester list sourced from the semester table with every view.
        // This keeps semester dropdowns across the whole portal in sync with the DB.
        View::composer('*', function ($view) {
            try {
                $semesters = DB::table('semester')
                    ->select('semester')
                    ->distinct()
                    ->whereNotNull('semester')
                    ->where('semester', '!=', '')
                    ->orderBy('semester')
                    ->pluck('semester')
                    ->values();
            } catch (\Exception $e) {
                // Fall back if the table is not available (e.g. during setup)
                $semesters = collect();
            }

            $view->with('semesters', $semesters);
        });
    }
}
