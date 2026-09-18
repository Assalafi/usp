<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentResultsController extends Controller
{
    /**
     * Download the logged-in student's approved results as a printable PDF.
     */
    public function downloadPdf(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $selectedSession = $request->input('session', session('system_session'));
        $isAllSessions = strtolower((string) $selectedSession) === 'all';
        $studentId = session('id_number');

        $resultsQuery = DB::table('results')
            ->leftJoin('course', 'results.code', '=', 'course.code')
            ->where([
                'results.username' => $studentId,
                'results.approve' => 'vc',
            ]);

        if (!$isAllSessions) {
            $resultsQuery->where('results.session', $selectedSession);
        }

        $results = $resultsQuery
            ->select('results.*', 'course.title as course_title')
            ->orderBy('results.session', 'DESC')
            ->orderBy('results.level', 'ASC')
            ->orderBy('results.semester', 'ASC')
            ->orderBy('results.code', 'ASC')
            ->get();

        $student = DB::table('students')
            ->where('username', $studentId)
            ->select('fullname', 'username', 'program', 'level')
            ->first();

        $historyQuery = DB::table('session_history')->where('username', $studentId);
        if (!$isAllSessions) {
            $historyQuery->where('session', $selectedSession);
        }
        $history = $historyQuery->orderBy('session', 'DESC')->first();

        $cgpaQuery = DB::table('results')
            ->where(['username' => $studentId, 'approve' => 'vc']);
        if (!$isAllSessions) {
            $cgpaQuery->where('session', '<=', $selectedSession);
        }
        $cgpaResults = $cgpaQuery->get(['unit', 'ugp']);
        $cgpaUnits = $cgpaResults->sum(fn ($result) => (float) ($result->unit ?? 0));
        $cgpaPoints = $cgpaResults->sum(fn ($result) => (float) ($result->ugp ?? 0));
        $calculatedCgpa = $cgpaUnits > 0 ? $cgpaPoints / $cgpaUnits : null;
        $historyCgpa = is_numeric($history->cgpa ?? null) ? (float) $history->cgpa : null;

        $gradeOrder = ['A', 'B', 'C', 'D', 'E', 'F'];
        $gradeCounts = collect($gradeOrder)->mapWithKeys(function ($grade) use ($results) {
            return [$grade => $results->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === $grade)->count()];
        });

        $viewData = [
            'results' => $results,
            'student' => $student,
            'selectedSession' => $selectedSession,
            'isAllSessions' => $isAllSessions,
            'historyCgpa' => $historyCgpa,
            'calculatedCgpa' => $calculatedCgpa,
            'gradeCounts' => $gradeCounts,
            'generatedAt' => now(),
        ];

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('Student.results-pdf', $viewData)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeStudentId = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $studentId);
        $filename = 'results-' . trim($safeStudentId, '-') . '-' . ($isAllSessions ? 'all-sessions' : preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $selectedSession)) . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => true]);
    }
}
