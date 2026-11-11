<?php

namespace App\Support;

use App\Http\Controllers\SystemSettingsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceAcademicSession
{
    public static function resolve(?string $requested): string
    {
        $requested = trim((string) $requested);
        if ($requested !== '' && !in_array(strtolower($requested), ['all', 'current'], true)) {
            if (DB::table('session')->where('title', $requested)->exists()) return $requested;
            throw ValidationException::withMessages(['session' => 'Please select an academic session from the list.']);
        }
        $current = SystemSettingsController::getSchoolFeesSession();
        if ($current && DB::table('session')->where('title', $current)->exists()) return $current;
        $current = DB::table('session')->where('status', '1')->orderByDesc('title')->value('title');
        if (!$current) $current = DB::table('session')->orderByDesc('title')->value('title');
        if (!$current) throw ValidationException::withMessages(['session' => 'An academic session must be configured before viewing collections.']);
        return $current;
    }
}
