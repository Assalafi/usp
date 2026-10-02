<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStudentResultsController extends Controller
{
    private function authorized(): bool
    {
        return session()->has('log') && in_array(session('accType'), ['Admin', 'Staff'], true);
    }

    private function payload(object $student, string $selectedSession): array
    {
        $isAllSessions = strtolower($selectedSession) === 'all';
        $resultsQuery = DB::table('results')
            ->leftJoin('course', 'results.code', '=', 'course.code')
            ->where('results.username', $student->username);

        if (!$isAllSessions) {
            $resultsQuery->where('results.session', $selectedSession);
        }

        $results = $resultsQuery
            ->select('results.*', 'course.title as course_title', 'course.unit as course_unit')
            ->orderBy('results.session', 'DESC')
            ->orderBy('results.level', 'ASC')
            ->orderBy('results.semester', 'ASC')
            ->orderBy('results.code', 'ASC')
            ->get();

        $historyQuery = DB::table('session_history')->where('username', $student->username);
        if (!$isAllSessions) {
            $historyQuery->where('session', $selectedSession);
        }
        $history = $historyQuery->orderBy('session', 'DESC')->first();

        $calculationUnits = $results->sum(function ($result) use ($isAllSessions) {
            $unit = $isAllSessions ? ($result->unit ?? null) : ($result->course_unit ?? null);
            return is_numeric($unit) && (float) $unit > 0 ? (float) $unit : 0;
        });
        $calculationProducts = $results->sum(fn ($result) => (float) ($result->ugp ?? 0));
        $calculatedCgpa = $calculationUnits > 0 ? $calculationProducts / $calculationUnits : null;
        $historyCgpa = is_numeric($history->cgpa ?? null) ? (float) $history->cgpa : null;
        $gradeOrder = ['A', 'B', 'C', 'D', 'E', 'F'];
        $gradeCounts = collect($gradeOrder)->mapWithKeys(fn ($grade) => [
            $grade => $results->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === $grade)->count(),
        ]);

        return [
            'student' => $student,
            'results' => $results,
            'selectedSession' => $selectedSession,
            'isAllSessions' => $isAllSessions,
            'historyCgpa' => $historyCgpa,
            'calculatedCgpa' => $calculatedCgpa,
            'calculationUnits' => $calculationUnits,
            'calculationProducts' => $calculationProducts,
            'gradeOrder' => $gradeOrder,
            'gradeCounts' => $gradeCounts,
            'academicStatus' => $history->status ?? null,
        ];
    }

    private function student(string $id): ?object
    {
        return DB::table('students')
            ->leftJoin('program', 'students.program', '=', 'program.code')
            ->where('students.id', $id)
            ->select('students.*', 'program.title as program_title')
            ->first();
    }

    public function show(Request $request, string $id)
    {
        if (!$this->authorized()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $student = $this->student($id);
        if (!$student) {
            abort(404, 'Student record not found.');
        }

        $currentSession = DB::table('session')->where('status', '1')->value('title') ?: session('system_session');
        $selectedSession = (string) $request->input('session', $currentSession ?: 'all');
        $sessions = DB::table('session')->select('title')->orderBy('title', 'DESC')->get();
        $data = $this->payload($student, $selectedSession);

        return view('main', array_merge($data, [
            'page' => 'student results',
            'sessions' => $sessions,
        ]));
    }

    public function data(Request $request, string $id)
    {
        if (!$this->authorized()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $student = $this->student($id);
        if (!$student) {
            return response()->json(['message' => 'Student record not found.'], 404);
        }

        $currentSession = DB::table('session')->where('status', '1')->value('title') ?: session('system_session');
        $selectedSession = (string) $request->input('session', $currentSession ?: 'all');
        $data = $this->payload($student, $selectedSession);

        return response()->json([
            'html' => view('Admin.student-results-content', $data)->render(),
            'session' => $selectedSession,
        ]);
    }
}
