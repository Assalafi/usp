@php
    $studentName = $student->fullname ?? session('fullname') ?? session('id_number');
    $studentId = $student->username ?? session('id_number');
    $sessionLabel = $isAllSessions ? 'All sessions' : ($selectedSession ?: 'Selected session');
    $recordedCgpa = is_numeric($historyCgpa ?? null) ? number_format((float) $historyCgpa, 2) : '-';
    $calculatedCgpaValue = is_numeric($calculatedCgpa ?? null) ? number_format((float) $calculatedCgpa, 2) : '-';
    $courseCount = $results->count();
    $unitCount = $results->sum(fn ($result) => (float) ($result->unit ?? 0));
    $passedCount = $results->filter(fn ($result) => !empty($result->grade) && strtoupper((string) $result->grade) !== 'F')->count();
    $failedCount = $results->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === 'F')->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Results - {{ $studentId }}</title>
    <style>
        @page { margin: 30px 34px 42px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; color: #20364f; font-size: 9px; }
        .page-footer { position: fixed; bottom: -25px; left: 0; right: 0; text-align: center; color: #7890a7; font-size: 7px; }
        .page-footer .page-number:after { content: counter(page); }
        .header { border-bottom: 2px solid #3ea1e4; padding-bottom: 10px; margin-bottom: 13px; }
        .header-table, .summary-table, .student-table, .results-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo-cell { width: 58px; }
        .logo { width: 48px; height: 48px; }
        .university { color: #173d67; font-size: 17px; font-weight: bold; letter-spacing: .2px; }
        .college { color: #5b7894; font-size: 9px; margin-top: 3px; }
        .document-label { text-align: right; color: #1765c6; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .document-session { text-align: right; color: #7890a7; font-size: 8px; margin-top: 3px; }
        .student-panel { background: #f3f8fc; border: 1px solid #d8e6f1; border-radius: 7px; padding: 10px 12px; margin-bottom: 10px; }
        .student-table td { padding: 2px 0; vertical-align: top; }
        .student-table .label { color: #7890a7; width: 12%; font-size: 7px; text-transform: uppercase; }
        .student-table .value { color: #203f62; width: 38%; font-size: 9px; font-weight: bold; }
        .summary-table { margin-bottom: 14px; }
        .summary-table td { width: 16.66%; border: 1px solid #dce8f2; padding: 7px 6px; background: #fff; }
        .summary-table td:first-child { border-radius: 6px 0 0 6px; }
        .summary-table td:last-child { border-radius: 0 6px 6px 0; }
        .summary-label { color: #7890a7; font-size: 7px; text-transform: uppercase; }
        .summary-value { display: block; color: #1765c6; font-size: 13px; font-weight: bold; margin-top: 2px; }
        .summary-value.official { color: #147a4a; }
        .summary-value.failed { color: #b42d3b; }
        .grade-summary { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 0 -5px 14px; }
        .grade-summary td { width: 16.66%; border: 1px solid #dce8f2; border-radius: 5px; padding: 6px; text-align: center; }
        .grade-summary .grade-label { display: block; font-size: 8px; font-weight: bold; }
        .grade-summary .grade-count { display: block; font-size: 12px; font-weight: bold; margin-top: 2px; }
        .grade-summary .grade-a { color: #137a46; background: #e8f7ee; border-color: #bce8ce; }
        .grade-summary .grade-b { color: #2c5fb7; background: #e8f0ff; border-color: #c8d9fb; }
        .grade-summary .grade-c { color: #a76700; background: #fff4dc; border-color: #f3d99a; }
        .grade-summary .grade-d { color: #7042a8; background: #f2eaff; border-color: #dcc6f8; }
        .grade-summary .grade-e { color: #b12f68; background: #ffeaf3; border-color: #f5c4d8; }
        .grade-summary .grade-f { color: #b42d3b; background: #ffe8eb; border-color: #f2bdc5; }
        .section-title { color: #173d67; font-size: 11px; font-weight: bold; margin: 13px 0 6px; }
        .results-table { border: 1px solid #cfdeea; }
        .results-table thead { display: table-header-group; }
        .results-table th { background: #1765c6; color: #fff; font-size: 7px; font-weight: bold; text-align: left; padding: 6px 5px; text-transform: uppercase; }
        .results-table td { border-top: 1px solid #e0e9f1; padding: 5px; vertical-align: middle; font-size: 8px; }
        .results-table tr:nth-child(even) td { background: #f8fbfd; }
        .results-table tr { page-break-inside: avoid; }
        .code { color: #1765c6; font-weight: bold; width: 12%; }
        .title { color: #304d6c; width: 32%; }
        .session { color: #6f859b; width: 10%; }
        .number { text-align: center; width: 7%; }
        .grade { text-align: center; font-weight: bold; width: 8%; }
        .grade-a { color: #137a46; background: #e8f7ee !important; }
        .grade-b { color: #2c5fb7; background: #e8f0ff !important; }
        .grade-c { color: #a76700; background: #fff4dc !important; }
        .grade-d { color: #7042a8; background: #f2eaff !important; }
        .grade-e { color: #b12f68; background: #ffeaf3 !important; }
        .grade-f { color: #b42d3b; background: #ffe8eb !important; }
        .grade-pending { color: #71839a; background: #eef3f8 !important; }
        .approved { color: #16834b; font-size: 7px; font-weight: bold; }
        .note { margin-top: 12px; padding: 7px 9px; border-left: 3px solid #3ea1e4; background: #f3f8fc; color: #6e849b; font-size: 7px; }
    </style>
</head>
<body>
    <div class="page-footer">University of Maiduguri Undergraduate Portal | Generated {{ $generatedAt->format('d M Y, h:i A') }} | Page <span class="page-number"></span></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell"><img class="logo" src="{{ public_path('uploads/logo.png') }}" alt="University logo"></td>
                <td><div class="university">UNIVERSITY OF MAIDUGURI</div><div class="college">Undergraduate Student Academic Record</div></td>
                <td><div class="document-label">Approved Results</div><div class="document-session">{{ $sessionLabel }}</div></td>
            </tr>
        </table>
    </div>

    <div class="student-panel">
        <table class="student-table">
            <tr><td class="label">Student</td><td class="value">{{ $studentName }}</td><td class="label">Matric number</td><td class="value">{{ $studentId }}</td></tr>
            <tr><td class="label">Programme</td><td class="value">{{ $student->program_title ?? $student->program ?? '-' }}</td><td class="label">Current level</td><td class="value">{{ $student->level ?? '-' }}</td></tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td><span class="summary-label">Official CGPA</span><span class="summary-value official">{{ $recordedCgpa }}</span></td>
            <td><span class="summary-label">Calculated CGPA</span><span class="summary-value">{{ $calculatedCgpaValue }}</span></td>
            <td><span class="summary-label">Courses</span><span class="summary-value">{{ $courseCount }}</span></td>
            <td><span class="summary-label">Units</span><span class="summary-value">{{ $unitCount }}</span></td>
            <td><span class="summary-label">Passed</span><span class="summary-value official">{{ $passedCount }}</span></td>
            <td><span class="summary-label">Carryovers</span><span class="summary-value failed">{{ $failedCount }}</span></td>
        </tr>
    </table>

    <div class="section-title">Grade summary</div>
    <table class="grade-summary">
        <tr>
            @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                <td class="grade-{{ strtolower($grade) }}"><span class="grade-label">Grade {{ $grade }}</span><span class="grade-count">{{ $gradeCounts[$grade] ?? 0 }}</span></td>
            @endforeach
        </tr>
    </table>

    <div class="section-title">Course results</div>
    <table class="results-table">
        <thead>
            <tr><th class="code">Code</th><th class="title">Course title</th><th class="session">Session</th><th class="number">CA</th><th class="number">Exam</th><th class="number">Total</th><th class="number">Unit</th><th class="grade">Grade</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($results as $result)
                @php
                    $grade = strtoupper((string) ($result->grade ?? ''));
                    $gradeClass = in_array($grade, ['A', 'B', 'C', 'D', 'E', 'F']) ? 'grade-' . strtolower($grade) : 'grade-pending';
                @endphp
                <tr>
                    <td class="code">{{ $result->code ?? '-' }}</td>
                    <td class="title">{{ $result->course_title ?? $result->title ?? 'Course result' }}</td>
                    <td class="session">{{ $result->session ?? '-' }}<br>{{ $result->level ?? '-' }} / {{ $result->semester ?? '-' }}</td>
                    <td class="number">{{ $result->ca ?? '-' }}</td>
                    <td class="number">{{ $result->exam ?? '-' }}</td>
                    <td class="number">{{ $result->total ?? '-' }}</td>
                    <td class="number">{{ $result->unit ?? '-' }}</td>
                    <td class="grade {{ $gradeClass }}">{{ $grade ?: '-' }}</td>
                    <td class="approved">Approved</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;color:#7890a7;padding:18px;">No approved results were found for this selection.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="note">This document contains approved results currently published on the University of Maiduguri student portal. The official CGPA is taken from the session history record; the calculated CGPA is provided for comparison.</div>
</body>
</html>
