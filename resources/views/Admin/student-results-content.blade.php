@php
    $studentResults = collect($results ?? []);
    $resultsByLevel = $studentResults->groupBy('level');
    $courseCount = $studentResults->count();
    $passedCount = $studentResults->filter(fn ($result) => !empty($result->grade) && strtoupper((string) $result->grade) !== 'F')->count();
    $failedCount = $studentResults->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === 'F')->count();
    $displaySessionLabel = ($isAllSessions ?? false) ? 'All sessions' : ($selectedSession ?? 'Selected session');
    $historyValue = is_numeric($historyCgpa ?? null) ? number_format((float) $historyCgpa, 2) : '-';
    $calculatedValue = is_numeric($calculatedCgpa ?? null) ? number_format((float) $calculatedCgpa, 2) : '-';
    $metricLabel = ($isAllSessions ?? false) ? 'CGPA' : 'GPA';
    $gradeTone = static function ($grade): string {
        $grade = strtoupper((string) $grade);
        return $grade === 'F' ? 'fail' : (in_array($grade, ['A', 'B', 'C', 'D', 'E'], true) ? 'pass' : 'pending');
    };
@endphp

<div class="admin-results-summary">
    <article><span class="admin-results-stat-icon blue"><i class="fas fa-book-open"></i></span><div><strong>{{ $courseCount }}</strong><small>Approved courses</small></div></article>
    <article><span class="admin-results-stat-icon green"><i class="fas fa-circle-check"></i></span><div><strong>{{ $passedCount }}</strong><small>Passed</small></div></article>
    <article><span class="admin-results-stat-icon red"><i class="fas fa-circle-exclamation"></i></span><div><strong>{{ $failedCount }}</strong><small>Carryovers</small></div></article>
    <article><span class="admin-results-stat-icon purple"><i class="fas fa-layer-group"></i></span><div><strong>{{ number_format((float) ($calculationUnits ?? 0), 0) }}</strong><small>{{ $isAllSessions ? 'Result units' : 'Course units' }}</small></div></article>
</div>

<section class="admin-results-card admin-grade-summary">
    <div class="admin-results-section-heading"><div><span>GRADE SUMMARY</span><h2>{{ $displaySessionLabel }}</h2><p>Approved results recorded for this selection.</p></div><div class="admin-results-gpa"><div><small>Recorded {{ $metricLabel }}</small><strong>{{ $historyValue }}</strong></div><div><small>Calculated {{ $metricLabel }}</small><strong>{{ $calculatedValue }}</strong></div></div></div>
    <div class="admin-grade-grid">
        @foreach ($gradeOrder as $grade)
            <div class="admin-grade-chip grade-{{ strtolower($grade) }}"><b>{{ $grade }}</b><strong>{{ $gradeCounts[$grade] ?? 0 }}</strong><small>{{ $grade === 'F' ? 'Carryover' : 'Grade' }}</small></div>
        @endforeach
    </div>
    <div class="admin-results-foot"><span><i class="fas fa-cubes"></i> {{ number_format((float) ($calculationUnits ?? 0), 0) }} units</span><span><i class="fas fa-check"></i> {{ $passedCount }} passed</span><span><i class="fas fa-rotate"></i> {{ $failedCount }} carryover</span><span><i class="fas fa-shield-check"></i> Approved only</span></div>
</section>

@if ($studentResults->isNotEmpty())
    <section class="admin-results-card">
        <div class="admin-results-section-heading"><div><span>COURSE GRADES</span><h2>Results at a glance</h2><p>Course codes and final grades for {{ strtolower($displaySessionLabel) }}.</p></div><span class="admin-results-count">{{ $courseCount }} results</span></div>
        <div class="admin-course-grade-grid">
            @foreach ($studentResults as $result)
                @php $grade = strtoupper((string) ($result->grade ?? '')); @endphp
                <div class="admin-course-grade"><div><strong>{{ $result->code }}</strong><small>{{ $result->course_title ?? $result->title ?? 'Course title not available' }}{{ ($isAllSessions ?? false) && $result->session ? ' · ' . $result->session : '' }}</small></div><b class="admin-grade-pill {{ $gradeTone($grade) }} grade-{{ strtolower($grade ?: 'pending') }}">{{ $grade ?: '—' }}</b></div>
            @endforeach
        </div>
    </section>

    <section class="admin-results-card admin-detailed-results">
        <div class="admin-results-section-heading"><div><span>DETAILED RESULTS</span><h2>Course breakdown</h2><p>Open a level to see marks, units, semester and approval.</p></div></div>
        @foreach ($resultsByLevel as $level => $levelResults)
            <details class="admin-result-level" {{ $loop->first ? 'open' : '' }}>
                <summary><span class="admin-level-badge">{{ $level ?: '—' }}</span><span><strong>{{ $level ?: 'Level not specified' }}</strong><small>{{ $levelResults->count() }} course{{ $levelResults->count() === 1 ? '' : 's' }}</small></span><i class="fas fa-chevron-down"></i></summary>
                <div class="admin-level-body">
                    @foreach ($levelResults->groupBy('semester') as $semester => $semesterResults)
                        <div class="admin-semester-label"><span>{{ $semester ?: 'Semester not specified' }}</span><b>{{ $semesterResults->count() }} result{{ $semesterResults->count() === 1 ? '' : 's' }}</b></div>
                        <div class="admin-detailed-grid">
                            @foreach ($semesterResults as $result)
                                @php $grade = strtoupper((string) ($result->grade ?? '')); @endphp
                                <article class="admin-detail-course">
                                    <div class="admin-detail-top"><div><strong>{{ $result->code }}</strong><small>{{ $result->course_title ?? $result->title ?? 'Course title not available' }}</small></div><b class="admin-grade-pill {{ $gradeTone($grade) }} grade-{{ strtolower($grade ?: 'pending') }}">{{ $grade ?: '—' }}</b></div>
                                    <div class="admin-marks"><div><small>CA</small><strong>{{ $result->ca ?? '—' }}</strong></div><div><small>Exam</small><strong>{{ $result->exam ?? '—' }}</strong></div><div><small>Total</small><strong>{{ $result->total ?? '—' }}</strong></div></div>
                                    <div class="admin-detail-meta"><span><i class="fas fa-cubes"></i> {{ $isAllSessions ? ($result->unit ?? '—') . ' result unit' : ($result->course_unit ?? '—') . ' course unit' }}</span><span><i class="fas fa-calendar"></i> {{ $semester ?: 'Not specified' }}</span><span class="approved"><i class="fas fa-shield-check"></i> Approved</span></div>
                                </article>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </section>
@else
    <div class="admin-results-empty"><i class="fas fa-file-circle-question"></i><h3>No approved results for this session</h3><p>Choose another session to view published results.</p></div>
@endif
