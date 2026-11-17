<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HodStaffRoleController extends Controller
{
    public function index()
    {
        [$departmentCode, $department] = $this->departmentContext();

        $staffMembers = $this->departmentStaff($departmentCode);
        $dso = $staffMembers->first(function ($staff) {
            return strtoupper(trim((string) ($staff->appointment ?? ''))) === 'DSO';
        });

        $freeCount = $staffMembers->filter(function ($staff) {
            return in_array(strtoupper(trim((string) ($staff->appointment ?? ''))), ['', 'STAFF'], true);
        })->count();

        return view('main', [
            'page' => 'hod staff roles',
            'title' => 'DEPARTMENT STAFF SUPPORT',
            'department' => $department,
            'staffMembers' => $staffMembers,
            'dso' => $dso,
            'freeCount' => $freeCount,
        ]);
    }

    public function assign(Request $request)
    {
        [$departmentCode] = $this->departmentContext();

        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = DB::transaction(function () use ($departmentCode, $validated) {
                // Serialize changes for this department. This prevents two
                // HOD requests arriving together from creating two DSO
                // assignments when the department currently has none.
                $department = DB::table('department')
                    ->whereRaw('UPPER(TRIM(code)) = ?', [$departmentCode])
                    ->lockForUpdate()
                    ->first(['id']);

                if (!$department) {
                    return ['ok' => false, 'message' => 'Your department could not be found.'];
                }

                $staff = DB::table('staff')
                    ->where('id', $validated['staff_id'])
                    ->whereRaw('UPPER(TRIM(department)) = ?', [$departmentCode])
                    ->lockForUpdate()
                    ->first(['id', 'name', 'appointment']);

                if (!$staff) {
                    return ['ok' => false, 'message' => 'That staff member is not part of your department.'];
                }

                $current = strtoupper(trim((string) ($staff->appointment ?? '')));
                if ($current === 'DSO') {
                    return ['ok' => true, 'message' => 'This staff member is already the Departmental Support Officer.'];
                }

                if (!in_array($current, ['', 'STAFF'], true)) {
                    return [
                        'ok' => false,
                        'message' => 'This staff member already has another responsibility (' . $staff->appointment . '). Remove it before assigning DSO.',
                    ];
                }

                $existing = DB::table('staff')
                    ->whereRaw('UPPER(TRIM(department)) = ?', [$departmentCode])
                    ->whereRaw("UPPER(TRIM(COALESCE(appointment, ''))) = 'DSO'")
                    ->lockForUpdate()
                    ->first(['id', 'name']);

                if ($existing && (int) $existing->id !== (int) $staff->id) {
                    return [
                        'ok' => false,
                        'message' => 'Your department already has a Departmental Support Officer. Remove that responsibility before choosing another staff member.',
                    ];
                }

                DB::table('staff')
                    ->where('id', $staff->id)
                    ->update([
                        'appointment' => 'DSO',
                        'updated_at' => now(),
                    ]);

                return ['ok' => true, 'message' => $staff->name . ' is now the Departmental Support Officer.'];
            }, 3);

            return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
        } catch (\Throwable $exception) {
            Log::error('HOD DSO assignment failed', [
                'department' => $departmentCode,
                'staff_id' => $validated['staff_id'],
                'username' => session('username'),
                'exception' => $exception,
            ]);

            return redirect()->back()->with('error', 'The responsibility could not be assigned right now. No changes were saved.');
        }
    }

    public function remove(Request $request)
    {
        [$departmentCode] = $this->departmentContext();

        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $updated = DB::transaction(function () use ($departmentCode, $validated) {
                DB::table('department')
                    ->whereRaw('UPPER(TRIM(code)) = ?', [$departmentCode])
                    ->lockForUpdate()
                    ->first(['id']);

                return DB::table('staff')
                    ->where('id', $validated['staff_id'])
                    ->whereRaw('UPPER(TRIM(department)) = ?', [$departmentCode])
                    ->whereRaw("UPPER(TRIM(COALESCE(appointment, ''))) = 'DSO'")
                    ->update([
                        'appointment' => 'STAFF',
                        'updated_at' => now(),
                    ]);
            }, 3);

            return redirect()->back()->with(
                $updated ? 'success' : 'error',
                $updated ? 'The Departmental Support Officer responsibility has been removed.' : 'No current DSO responsibility was found for that staff member.'
            );
        } catch (\Throwable $exception) {
            Log::error('HOD DSO removal failed', [
                'department' => $departmentCode,
                'staff_id' => $validated['staff_id'],
                'username' => session('username'),
                'exception' => $exception,
            ]);

            return redirect()->back()->with('error', 'The responsibility could not be removed right now. No changes were saved.');
        }
    }

    private function departmentContext(): array
    {
        if (!session()->has('log')) {
            abort(403, 'Please sign in to continue.');
        }

        $isHod = strtolower(trim((string) session('accType'))) === 'staff'
            && strtoupper(trim((string) session('appointment'))) === 'HOD';

        if (!$isHod) {
            abort(403, 'This page is available to Heads of Department only.');
        }

        $departmentCode = strtoupper(trim((string) session('department')));
        if ($departmentCode === '') {
            abort(403, 'Your department has not been set. Please contact the administrator.');
        }

        $department = DB::table('department')
            ->whereRaw('UPPER(TRIM(code)) = ?', [$departmentCode])
            ->first(['code', 'title']);

        if (!$department) {
            abort(403, 'Your department could not be found. Please contact the administrator.');
        }

        return [$departmentCode, $department];
    }

    private function departmentStaff(string $departmentCode)
    {
        return DB::table('staff as s')
            ->leftJoin('designations as d', 'd.id', '=', 's.designation_id')
            ->where('s.status', '1')
            ->whereRaw('UPPER(TRIM(s.department)) = ?', [$departmentCode])
            ->select([
                's.id',
                's.username',
                's.name',
                's.appointment',
                's.staff_category',
                's.phone',
                's.picture',
                DB::raw('COALESCE(NULLIF(s.current_rank, \'\'), NULLIF(d.name, \'\')) as designation'),
            ])
            ->orderByRaw("CASE WHEN UPPER(TRIM(COALESCE(s.appointment, ''))) = 'DSO' THEN 0 ELSE 1 END")
            ->orderBy('s.name')
            ->get();
    }
}
