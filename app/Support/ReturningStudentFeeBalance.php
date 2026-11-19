<?php

namespace App\Support;

use App\Http\Controllers\SystemSettingsController;
use Illuminate\Support\Facades\DB;

/**
 * Calculates the programme-fee balance a returning undergraduate must clear
 * before entering the new academic session.
 *
 * The recovery session is deliberately separate from the active system
 * session. It defaults to 2025/2026 for the current clearance exercise and
 * can be changed later through the returning_fee_recovery_session setting.
 */
final class ReturningStudentFeeBalance
{
    public static function forUser($userId, ?string $recoverySession = null): array
    {
        $userId = (string) $userId;
        $activeSession = trim((string) SystemSettingsController::getSystemSession());
        $recoverySession = trim((string) ($recoverySession
            ?: SystemSettingsController::get('returning_fee_recovery_session', '2025/2026')));

        $empty = [
            'is_returning' => false,
            'session' => $recoverySession,
            'assessed' => 0.0,
            'paid' => 0.0,
            'balance' => 0.0,
            'sessions' => [],
            'has_history' => false,
        ];

        if ($userId === '' || $recoverySession === '') {
            return $empty;
        }

        $student = DB::table('students')
            ->where('user_id', $userId)
            ->select('username', 'program', 'session_of_entry')
            ->first();

        if (!$student) {
            return $empty;
        }

        $isReturning = trim((string) $student->session_of_entry) !== ''
            && trim((string) $student->session_of_entry) !== $activeSession;
        $empty['is_returning'] = $isReturning;
        if (!$isReturning) {
            return $empty;
        }

        $recoveryStart = self::sessionStart($recoverySession);
        $activeStart = self::sessionStart($activeSession);
        $history = DB::table('session_history')
            ->where('username', (string) $student->username)
            ->get(['session', 'level', 'program']);

        $debtRows = $history->filter(function ($row) use ($recoveryStart, $activeStart) {
            $start = self::sessionStart((string) ($row->session ?? ''));
            if ($start === null || $start > $recoveryStart) {
                return false;
            }
            return $activeStart === null || $start < $activeStart;
        })->values();

        if ($debtRows->isEmpty()) {
            return $empty;
        }

        $assessed = 0.0;
        $sessions = [];
        foreach ($debtRows as $row) {
            $session = trim((string) ($row->session ?? ''));
            if ($session === '') {
                continue;
            }
            $sessions[] = $session;
            $program = trim((string) ($row->program ?? '')) ?: trim((string) $student->program);
            $type = $session === trim((string) $student->session_of_entry) ? 'NEW' : 'RETURNING';
            $amount = DB::table('school_fees')
                ->where(['program' => $program, 'level' => $row->level, 'type' => $type])
                ->value('amount');
            $assessed += max(0.0, (float) ($amount ?? 0));
        }

        $sessions = array_values(array_unique($sessions));
        $paid = $sessions === [] ? 0.0 : (float) DB::table('invoices')
            ->where('username', $userId)
            ->where('description', 'UNIVERSITY OF MAIDUGURI-1000127 FEES')
            ->whereIn('status', ['Paid', 'Successful', 'Success'])
            ->whereIn('session', $sessions)
            // Older invoices may store a zero base_amount.  Treat zero like
            // an unset value so those genuine payments still reduce debt.
            ->sum(DB::raw('COALESCE(NULLIF(base_amount, 0), amount)'));

        return [
            'is_returning' => true,
            'session' => $recoverySession,
            'assessed' => $assessed,
            'paid' => $paid,
            'balance' => max(0.0, $assessed - $paid),
            'sessions' => $sessions,
            'has_history' => true,
        ];
    }

    private static function sessionStart(string $session): ?int
    {
        if (preg_match('/^(\d{4})\s*\//', trim($session), $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
