@php
    $studentResults = collect($data ?? []);
    $resultsByLevel = $studentResults->groupBy('level');
    $courseCount = $studentResults->count();
    $failedCount = $studentResults->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === 'F')->count();
    $passedCount = $studentResults->filter(fn ($result) => !empty($result->grade) && strtoupper((string) $result->grade) !== 'F')->count();
    $gradeOrder = ['A', 'B', 'C', 'D', 'E', 'F'];
    $gradeCounts = collect($gradeOrder)->mapWithKeys(fn ($grade) => [$grade => $studentResults->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === $grade)->count()]);
    $studentName = $student->fullname ?? session('fullname') ?? session('id_number');
    $displaySession = $selectedSession ?? request('session', session('system_session'));
    $isAllSessions = strtolower((string) $displaySession) === 'all';
    $displaySessionLabel = $isAllSessions ? 'All sessions' : $displaySession;
    $historyCgpaValue = is_numeric($historyCgpa ?? null) ? number_format((float) $historyCgpa, 2) : '-';
    $calculatedCgpaValue = is_numeric($calculatedCgpa ?? null) ? number_format((float) $calculatedCgpa, 2) : '-';
    $calculationUnit = fn ($result) => $isAllSessions
        ? (float) ($result->unit ?? 0)
        : (float) ($result->course_unit ?? 0);
    $unitCount = $studentResults->sum($calculationUnit);
    $cgpaRows = $studentResults;
    $cgpaExcludedRows = $studentResults->filter(fn ($result) => !is_numeric($result->unit ?? null) || (float) $result->unit <= 0);
    $cgpaUnitsTotal = $studentResults->sum($calculationUnit);
    $cgpaProductsTotal = $studentResults->sum(fn ($result) => (float) ($result->ugp ?? 0));
    $popupCalculatedCgpa = $cgpaUnitsTotal > 0 ? $cgpaProductsTotal / $cgpaUnitsTotal : null;
@endphp

<div class="main-body ug-results-page">
    <div class="page-wrapper">
        <div class="ug-results-shell">
            <div class="ug-results-filter-bar">
                <div class="ug-results-identity"><div class="ug-identity-name"><span class="ug-results-kicker">{{ $studentName }}</span><strong>{{ $displaySessionLabel }}</strong></div><div class="ug-cgpa-summary" aria-label="{{ $isAllSessions ? 'CGPA summary' : 'GPA summary' }}"><button type="button" class="ug-cgpa-card" data-cgpa-open="recorded" aria-haspopup="dialog" aria-controls="cgpaExplanation"><span><small>{{ $isAllSessions ? 'Recorded CGPA' : 'Recorded GPA' }}</small><b>{{ $historyCgpaValue }}</b></span><i class="fas fa-circle-info" aria-hidden="true"></i><em>View details</em></button><button type="button" class="ug-cgpa-card" data-cgpa-open="calculated" aria-haspopup="dialog" aria-controls="cgpaExplanation"><span><small>{{ $isAllSessions ? 'Calculated CGPA' : 'Session GPA' }}</small><b>{{ $calculatedCgpaValue }}</b></span><i class="fas fa-calculator" aria-hidden="true"></i><em>View calculation</em></button></div></div>
                <form class="ug-results-session" action="{{ url('/student-result') }}" method="GET">
                    <label for="resultSession">Academic session</label>
                    <select id="resultSession" name="session" onchange="this.form.submit()" aria-label="Choose academic session">
                        <option value="all" {{ $isAllSessions ? 'selected' : '' }}>All sessions</option>
                        @foreach ($sessions as $sessionRow)
                            <option value="{{ $sessionRow->title }}" {{ $displaySession == $sessionRow->title ? 'selected' : '' }}>
                                {{ $sessionRow->title }}{{ $displaySession == $sessionRow->title && $sessionRow->title == session('system_session') ? ' (Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <section class="ug-result-layer ug-grade-layer" aria-labelledby="gradeSummaryTitle">
                <div class="ug-layer-heading"><div><span class="ug-layer-number">1</span><div><h2 id="gradeSummaryTitle">Grade summary</h2><p>A quick view of your grades for {{ strtolower($displaySessionLabel) }}.</p></div></div><span class="ug-layer-total">{{ $courseCount }} courses</span></div>
                <div class="ug-grade-chips">
                    @foreach ($gradeOrder as $grade)
                        <div class="ug-grade-chip grade-{{ strtolower($grade) }}"><span>{{ $grade }}</span><strong>{{ $gradeCounts[$grade] }}</strong><small>{{ $grade === 'F' ? 'Carryover' : 'Grade' }}</small></div>
                    @endforeach
                </div>
                <div class="ug-grade-footer"><span><i class="fas fa-layer-group"></i> {{ $unitCount }} total units</span><span><i class="fas fa-circle-check"></i> {{ $passedCount }} passed</span><span><i class="fas fa-circle-exclamation"></i> {{ $failedCount }} carryover</span></div>
            </section>

            @if ($studentResults->isNotEmpty())
                <section class="ug-result-layer ug-course-grade-layer" aria-labelledby="courseGradesTitle">
                    <div class="ug-layer-heading"><div><span class="ug-layer-number">2</span><div><h2 id="courseGradesTitle">Course grades</h2><p>Each course and its final grade.</p></div></div><span class="ug-layer-total">{{ $courseCount }} results</span></div>
                    <div class="ug-course-grade-list" id="courseGradeList">
                        @foreach ($studentResults as $result)
                            @php $compactGrade = strtoupper((string) ($result->grade ?? '')); $compactTone = $compactGrade === 'F' ? 'fail' : (in_array($compactGrade, ['A', 'B', 'C', 'D', 'E']) ? 'pass' : 'pending'); @endphp
                            <div class="ug-course-grade-row" data-course-grade data-search-text="{{ strtolower(($result->code ?? '') . ' ' . ($result->course_title ?? $result->title ?? '')) }}"><span><strong>{{ $result->code }}</strong><small>{{ $result->course_title ?? $result->title ?? 'Course result' }}{{ $isAllSessions && !empty($result->session) ? ' · ' . $result->session : '' }}</small></span><b class="ug-grade {{ $compactTone }} grade-{{ strtolower($compactGrade ?: 'pending') }}">{{ $compactGrade ?: '—' }}</b></div>
                        @endforeach
                    </div>
                </section>
                <section class="ug-result-layer ug-detail-layer" aria-labelledby="detailTitle">
                    <div class="ug-layer-heading"><div><span class="ug-layer-number">3</span><div><h2 id="detailTitle">Detailed results</h2><p>Open a level to view the complete result breakdown.</p></div></div></div>
                    <div class="ug-results-toolbar">
                        <div><strong>Detailed result structure</strong><span>Marks, total, units, semester and approval status</span></div>
                        <div class="ug-results-toolbar-actions"><label class="ug-results-search"><i class="fas fa-search"></i><input id="resultSearch" type="search" placeholder="Search results" autocomplete="off"></label><a class="ug-results-print" href="{{ route('student.result.pdf', ['session' => $displaySession]) }}"><i class="fas fa-file-pdf"></i><span>Download PDF</span></a></div>
                    </div>
                    <div id="resultGroups">
                    @foreach ($resultsByLevel as $level => $levelResults)
                        <details class="ug-result-level" open>
                            <summary><span class="ug-result-level-badge">{{ $level }}</span><span><strong>{{ $level }} level</strong><small>{{ $levelResults->count() }} course{{ $levelResults->count() === 1 ? '' : 's' }}</small></span><i class="fas fa-chevron-down"></i></summary>
                            <div class="ug-result-level-body">
                                @foreach ($levelResults->groupBy('semester') as $semester => $semesterResults)
                                    <div class="ug-result-semester">
                                        <div class="ug-result-semester-heading"><span>{{ $semester ?: 'Semester not specified' }}</span><b>{{ $semesterResults->count() }} result{{ $semesterResults->count() === 1 ? '' : 's' }}</b></div>
                                        <div class="ug-result-cards">
                                            @foreach ($semesterResults as $result)
                                                @php
                                                    $grade = strtoupper((string) ($result->grade ?? ''));
                                                    $gradeTone = $grade === 'F' ? 'fail' : (in_array($grade, ['A', 'B', 'C', 'D', 'E']) ? 'pass' : 'pending');
                                                    $searchText = strtolower(($result->code ?? '') . ' ' . ($result->course_title ?? $result->title ?? ''));
                                                @endphp
                                                <article class="ug-result-card" data-result-card data-search-text="{{ $searchText }}">
                                                    <div class="ug-result-card-top"><div><strong class="ug-result-code">{{ $result->code }}</strong><span class="ug-result-title">{{ $result->course_title ?? $result->title ?? 'Course result' }}</span></div><span class="ug-grade {{ $gradeTone }} grade-{{ strtolower($grade ?: 'pending') }}">{{ $grade ?: '—' }}</span></div>
                                                    <div class="ug-result-marks"><div><small>CA</small><strong>{{ $result->ca ?? '—' }}</strong></div><div><small>Exam</small><strong>{{ $result->exam ?? '—' }}</strong></div><div class="total"><small>Total</small><strong>{{ $result->total ?? '—' }}</strong></div></div>
                                                    <div class="ug-result-meta"><span><i class="fas fa-cubes"></i> {{ is_numeric($isAllSessions ? ($result->unit ?? null) : ($result->course_unit ?? null)) ? number_format((float) ($isAllSessions ? ($result->unit ?? 0) : ($result->course_unit ?? 0)), 0) : '-' }} {{ $isAllSessions ? 'result unit' : 'course unit' }}</span><span><i class="fas fa-calendar"></i> {{ $semester ?: 'Semester not specified' }}</span>@if ($isAllSessions && !empty($result->session))<span><i class="fas fa-clock"></i> {{ $result->session }}</span>@endif<span class="ug-approved"><i class="fas fa-shield-check"></i> Approved</span></div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                    </div>
                </section>
                <div id="noResultMatch" class="ug-results-empty" hidden><i class="fas fa-search"></i><div><strong>No matching course</strong><small>Try a different course code or title.</small></div></div>
            @else
                <div class="ug-results-empty ug-results-empty-large"><span><i class="fas fa-file-circle-question"></i></span><h3>No approved results for this session</h3><p>Results will appear here after they have been approved and published. You can choose another session above.</p></div>
            @endif

            <div class="ug-cgpa-modal" id="cgpaExplanation" data-cgpa-scope="{{ $isAllSessions ? 'cgpa' : 'gpa' }}" hidden role="dialog" aria-modal="true" aria-labelledby="cgpaExplanationTitle" aria-describedby="cgpaExplanationDescription">
                <div class="ug-cgpa-modal-backdrop" data-cgpa-close></div>
                <div class="ug-cgpa-dialog" role="document">
                    <div class="ug-cgpa-dialog-header">
                        <div><span class="ug-results-kicker">{{ $isAllSessions ? 'CGPA explanation' : 'GPA explanation' }}</span><h2 id="cgpaExplanationTitle">{{ $isAllSessions ? 'How your CGPA was calculated' : 'How your session GPA was calculated' }}</h2><p id="cgpaExplanationDescription">This breakdown uses approved results for {{ strtolower($displaySessionLabel) }}.</p></div>
                        <button type="button" class="ug-cgpa-close" data-cgpa-close aria-label="Close CGPA explanation"><i class="fas fa-times" aria-hidden="true"></i></button>
                    </div>
                    <div class="ug-cgpa-dialog-summary">
                        <div><small>{{ $isAllSessions ? 'Recorded CGPA' : 'Recorded GPA' }}</small><strong>{{ $historyCgpaValue }}</strong><span>From session history</span></div>
                        <div><small>{{ $isAllSessions ? 'Calculated CGPA' : 'Session GPA' }}</small><strong>{{ $calculatedCgpaValue }}</strong><span>From approved results</span></div>
                        <div><small>Total units</small><strong>{{ number_format($cgpaUnitsTotal, 0) }}</strong><span>Used in denominator</span></div>
                        <div><small>Products</small><strong>{{ number_format($cgpaProductsTotal, 2) }}</strong><span>Unit x grade point product</span></div>
                    </div>
                    <div class="ug-cgpa-formula"><span>Calculation method</span><strong>{{ $isAllSessions ? 'CGPA' : 'GPA' }} = total products / total units</strong>@if ($cgpaUnitsTotal > 0)<p>{{ number_format($cgpaProductsTotal, 2) }} / {{ number_format($cgpaUnitsTotal, 0) }} = {{ number_format((float) $popupCalculatedCgpa, 2) }}</p>@else<p class="ug-cgpa-unavailable">Not available - there are no valid denominator units.</p>@endif</div>
                    <div class="ug-cgpa-breakdown">
<div class="ug-cgpa-breakdown-heading"><div><strong>Course-by-course breakdown</strong><small>{{ $isAllSessions ? 'CGPA uses stored result units for the total. A resit product is included, but its 0 result units add no new units.' : 'GPA uses the course unit for every course. Resit rows are flagged when their stored result unit is 0.' }}</small></div><span>{{ $cgpaRows->count() }} courses - {{ $cgpaExcludedRows->count() }} resit rows</span></div>
                        @if ($studentResults->isNotEmpty())
<div class="ug-cgpa-table-wrap"><table class="ug-cgpa-table"><thead><tr><th>Course</th><th>Grade</th><th>Units used</th><th>Product</th><th>Status</th></tr></thead><tbody>
@foreach ($studentResults as $cgpaRow)
                                    @php
                                        $popupGrade = strtoupper((string) ($cgpaRow->grade ?? ''));
                                        $popupResultUnit = is_numeric($cgpaRow->unit ?? null) ? (float) $cgpaRow->unit : 0;
                                        $popupCourseUnit = is_numeric($cgpaRow->course_unit ?? null) ? (float) $cgpaRow->course_unit : 0;
                                        $popupUnit = $isAllSessions ? $popupResultUnit : $popupCourseUnit;
                                        $popupIsResit = $popupResultUnit <= 0;
                                    @endphp
                                    <tr class="{{ $popupIsResit ? 'ug-cgpa-resit-row' : '' }}"><td><strong>{{ $cgpaRow->code }}</strong><small>{{ $cgpaRow->course_title ?? $cgpaRow->title ?? 'Course result' }}{{ $isAllSessions && !empty($cgpaRow->session) ? ' - ' . $cgpaRow->session : '' }}</small></td><td><span class="ug-cgpa-grade grade-{{ strtolower($popupGrade ?: 'pending') }}">{{ $popupGrade ?: '-' }}</span></td><td>{{ $popupUnit > 0 ? number_format($popupUnit, 0) : '-' }}<small>{{ $isAllSessions ? 'result unit' : 'course unit' }}</small></td><td>{{ is_numeric($cgpaRow->ugp ?? null) ? number_format((float) $cgpaRow->ugp, 2) : '-' }}</td><td>@if ($popupIsResit)<span class="ug-cgpa-excluded"><i class="fas fa-circle-info"></i> {{ $isAllSessions ? 'Resit - 0 result units; product included' : 'Resit - course unit used' }}</span>@else<span class="ug-cgpa-included">Included</span>@endif</td></tr>
                                @endforeach
                             </tbody></table></div>
                        @else
                            <div class="ug-cgpa-no-data"><i class="fas fa-circle-info"></i><span>No approved course results are available for this selection.</span></div>
                        @endif
                    </div>
<div class="ug-cgpa-note"><i class="fas fa-lightbulb" aria-hidden="true"></i><span>The recorded {{ $isAllSessions ? 'CGPA' : 'GPA' }} is the official value saved in session history. The calculated {{ $isAllSessions ? 'CGPA' : 'GPA' }} uses the product from every approved result. {{ $isAllSessions ? 'For CGPA, the denominator uses stored result units.' : 'For GPA, the denominator uses course units.' }}</span></div>
                    @if ($cgpaExcludedRows->isNotEmpty())
<div class="ug-cgpa-resit-notice"><i class="fas fa-rotate"></i><span>{{ $cgpaExcludedRows->count() }} result{{ $cgpaExcludedRows->count() === 1 ? '' : 's' }} have 0 stored result units. {{ $isAllSessions ? 'Their product remains in the CGPA total, while their zero units do not increase the denominator.' : 'The matching course unit is used for this session GPA.' }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ug-results-page{background:#f4f7fb;min-height:calc(100vh - 70px);padding:clamp(12px,2vw,28px)}.ug-results-shell{max-width:1180px;margin:0 auto}.ug-results-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;background:#fff;border:1px solid #e1eaf3;border-left:5px solid #3ea1e4;border-radius:18px;padding:clamp(20px,3vw,30px);box-shadow:0 8px 24px rgba(29,61,103,.06)}.ug-results-hero-copy{display:flex;align-items:center;gap:15px}.ug-results-icon{width:50px;height:50px;display:grid;place-items:center;border-radius:14px;background:#eaf5fd;color:#258ac8;font-size:1.35rem;flex:0 0 auto}.ug-results-kicker{display:block;color:#39769b;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.ug-results-hero h1{color:#183b64;font-size:clamp(1.65rem,3vw,2.25rem);margin:6px 0 4px;font-weight:800}.ug-results-hero p{color:#6c8097;margin:0;font-size:.9rem}.ug-results-session{background:#f4f9fe;border:1px solid #dce9f5;border-radius:12px;padding:10px 12px;min-width:210px}.ug-results-session label{display:block;color:#55738f;font-size:.7rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}.ug-results-session select{width:100%;background:#fff;color:#24578e;border:1px solid #cfe0ee;border-radius:8px;font-weight:700;padding:8px 9px}
    .ug-results-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0}.ug-result-stat{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e4ebf3;border-radius:13px;padding:13px;min-width:0}.ug-result-stat strong,.ug-result-stat small{display:block}.ug-result-stat strong{color:#19365e;font-size:1.2rem}.ug-result-stat small{color:#76879c;font-size:.72rem;margin-top:2px}.ug-result-stat-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;flex:0 0 auto}.ug-result-stat-icon.blue{background:#e8f1ff;color:#2765cb}.ug-result-stat-icon.cyan{background:#e7f8fc;color:#1a91ab}.ug-result-stat-icon.green{background:#e7f8ef;color:#16834b}.ug-result-stat-icon.red{background:#fff0f0;color:#c24b58}
    .ug-results-context{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#173a62;color:#fff;border-radius:15px;padding:18px 20px;margin-bottom:14px}.ug-results-context .ug-results-kicker{color:#a9d5ef}.ug-results-context h2{margin:4px 0;color:#fff;font-size:1.15rem}.ug-results-context p{margin:0;color:#c7d9e9;font-size:.78rem}.ug-results-print{display:inline-flex;align-items:center;border:1px solid #cfe0ee;background:#eaf4ff;color:#1765c6;border-radius:9px;padding:9px 12px;font-weight:700;cursor:pointer;white-space:nowrap;text-decoration:none}.ug-results-print:hover{background:#dcefff;color:#12559f}.ug-results-print i{margin-right:6px}.ug-results-toolbar{display:flex;justify-content:space-between;align-items:center;gap:14px;background:#fff;border:1px solid #e3ebf3;border-radius:13px;padding:13px 15px;margin-bottom:12px}.ug-results-toolbar strong,.ug-results-toolbar span{display:block}.ug-results-toolbar strong{color:#27496e;font-size:.95rem}.ug-results-toolbar span{color:#8493a7;font-size:.72rem;margin-top:2px}.ug-results-search{display:flex;align-items:center;gap:8px;border:1px solid #d8e3ee;border-radius:9px;background:#f9fbfd;padding:7px 10px;color:#7b8da2;min-width:220px}.ug-results-search input{border:0;background:transparent;outline:0;width:100%;font-size:.8rem;color:#29496b}.ug-result-level{background:#fff;border:1px solid #e2eaf3;border-radius:14px;margin-bottom:11px;overflow:hidden}.ug-result-level summary{display:flex;align-items:center;gap:11px;list-style:none;cursor:pointer;padding:14px 16px}.ug-result-level summary::-webkit-details-marker{display:none}.ug-result-level summary>span:nth-child(2){display:flex;flex-direction:column;flex:1}.ug-result-level summary strong{color:#27496e;font-size:.9rem}.ug-result-level summary small{color:#8391a5;font-size:.72rem;margin-top:2px}.ug-result-level summary>i{color:#7e91a7;font-size:.78rem;transition:transform .2s}.ug-result-level[open] summary>i{transform:rotate(180deg)}.ug-result-level-badge{width:35px;height:35px;display:grid;place-items:center;border-radius:10px;background:#eaf4ff;color:#1765c6;font-weight:800}.ug-result-level-body{border-top:1px solid #edf2f7;padding:14px 16px}.ug-result-semester{margin-bottom:18px}.ug-result-semester:last-child{margin-bottom:0}.ug-result-semester-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:9px;color:#426487;font-size:.78rem;font-weight:800}.ug-result-semester-heading b{font-size:.7rem;color:#8a98a9;font-weight:600}.ug-result-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ug-result-card{border:1px solid #e4ebf3;border-radius:12px;padding:13px;background:#fff}.ug-result-card:hover{border-color:#abd1ed;box-shadow:0 3px 12px rgba(44,111,192,.06)}.ug-result-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.ug-result-card-top>div{min-width:0}.ug-result-code,.ug-result-title{display:block}.ug-result-code{color:#1765c6;font-size:.86rem}.ug-result-title{color:#4a5d73;font-size:.78rem;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-grade{min-width:34px;text-align:center;border-radius:8px;padding:6px 8px;font-weight:800;font-size:.8rem}.ug-grade.pass{background:#e7f8ef;color:#16834b}.ug-grade.fail{background:#fff0f0;color:#c24b58}.ug-grade.pending{background:#eef3f8;color:#71839a}.ug-result-marks{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;border-top:1px solid #edf2f7;border-bottom:1px solid #edf2f7;margin:12px 0 9px;padding:9px 0}.ug-result-marks div{text-align:center}.ug-result-marks small,.ug-result-marks strong{display:block}.ug-result-marks small{color:#8594a7;font-size:.65rem}.ug-result-marks strong{color:#304d6c;font-size:.9rem;margin-top:2px}.ug-result-marks .total{background:#f1f7fc;border-radius:7px;padding:4px}.ug-result-marks .total strong{color:#1765c6}.ug-result-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;color:#8492a4;font-size:.68rem}.ug-result-meta i{color:#6e9bc1;margin-right:3px}.ug-result-meta .ug-approved{color:#16834b;font-weight:700}.ug-result-meta .ug-approved i{color:#16834b}.ug-results-empty{display:flex;align-items:center;justify-content:center;gap:12px;background:#fff;border:1px solid #e4ebf3;border-radius:14px;padding:20px;color:#7b8ca0}.ug-results-empty>i{font-size:1.3rem;color:#8aa5bd}.ug-results-empty strong,.ug-results-empty small{display:block}.ug-results-empty strong{color:#47617e;font-size:.85rem}.ug-results-empty small{font-size:.75rem;margin-top:3px}.ug-results-empty-large{display:block;text-align:center;padding:58px 18px}.ug-results-empty-large>span{width:58px;height:58px;display:grid;place-items:center;border-radius:50%;background:#eaf4ff;color:#3981bd;font-size:1.45rem;margin:0 auto 13px}.ug-results-empty-large h3{color:#2d4a6b;font-size:1.1rem;margin:0 0 6px}.ug-results-empty-large p{color:#7c8ca0;font-size:.82rem;max-width:440px;margin:0 auto}
    @media (max-width:767px){.ug-results-page{padding:10px;background:#f6f8fb}.ug-results-hero{display:block;border-radius:15px;padding:19px 15px}.ug-results-hero-copy{align-items:flex-start;gap:11px}.ug-results-icon{width:40px;height:40px;font-size:1.05rem}.ug-results-hero h1{font-size:1.55rem}.ug-results-hero p{font-size:.8rem}.ug-results-session{margin-top:16px;min-width:0}.ug-results-summary{grid-template-columns:repeat(2,1fr);gap:8px;margin:10px 0}.ug-result-stat{padding:10px;gap:8px}.ug-result-stat-icon{width:32px;height:32px;font-size:.85rem}.ug-result-stat strong{font-size:1rem}.ug-result-stat small{font-size:.67rem}.ug-results-context{display:block;border-radius:13px;padding:15px;margin-bottom:10px}.ug-results-context h2{font-size:1rem}.ug-results-context p{font-size:.7rem}.ug-results-print{margin-top:12px;width:100%;padding:8px}.ug-results-toolbar{display:block;padding:12px;margin-bottom:9px}.ug-results-search{margin-top:10px;min-width:0;width:100%}.ug-result-level summary{padding:12px}.ug-result-level-body{padding:11px 10px}.ug-result-cards{grid-template-columns:1fr}.ug-result-card{padding:12px}.ug-result-meta{gap:7px}.ug-result-semester-heading{font-size:.74rem}.ug-result-semester-heading b{font-size:.66rem}}
    @media print{.ug-results-page{background:#fff;padding:0}.ug-results-hero,.ug-results-summary,.ug-results-toolbar,.ug-results-print,.ug-results-session{box-shadow:none}.ug-results-hero{border:1px solid #ccc}.ug-results-print,.ug-results-search{display:none}.ug-result-level{break-inside:avoid}.pcoded-navbar,.pcoded-header,.loader-bg{display:none!important}.ug-results-page{margin:0!important}.ug-result-card:hover{box-shadow:none}}
    .ug-results-filter-bar{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#fff;border:1px solid #e1eaf3;border-radius:13px;padding:12px 15px;margin-bottom:12px}.ug-results-filter-bar>div{min-width:0}.ug-results-filter-bar strong,.ug-results-filter-bar .ug-results-kicker{display:block}.ug-results-filter-bar strong{color:#27496e;font-size:.95rem;margin-top:3px}.ug-results-filter-bar .ug-results-kicker{font-size:.68rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-results-filter-bar .ug-results-session{min-width:205px;padding:8px 10px}.ug-session-standing{display:flex;align-items:center;gap:14px;border-top:1px solid #dbe8f3;margin-top:8px;padding-top:8px}.ug-session-standing>div{display:flex;align-items:baseline;gap:4px}.ug-session-standing small{color:#66809a;font-size:.61rem;text-transform:uppercase;letter-spacing:.04em}.ug-session-standing strong{color:#1765c6;font-size:1.1rem}.ug-session-standing span{color:#7d91a5;font-size:.65rem}.ug-session-standing b{color:#16834b;font-size:.72rem}.ug-results-toolbar-actions{display:flex;align-items:center;gap:8px}.ug-results-toolbar-actions .ug-results-print{background:#eaf4ff;border-color:#d3e6f6;color:#1765c6;padding:7px 10px;font-size:.74rem}.ug-result-layer{background:#fff;border:1px solid #e2eaf3;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 5px 16px rgba(29,61,103,.04)}.ug-layer-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.ug-layer-heading>div{display:flex;align-items:center;gap:10px}.ug-layer-number{width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#eaf4ff;color:#1765c6;font-size:.78rem;font-weight:800;flex:0 0 auto}.ug-layer-heading h2{margin:0;color:#27496e;font-size:1rem}.ug-layer-heading p{margin:3px 0 0;color:#8493a7;font-size:.72rem}.ug-layer-total{color:#7890a7;font-size:.72rem;font-weight:700;white-space:nowrap}.ug-grade-chips{display:grid;grid-template-columns:repeat(6,1fr);gap:8px}.ug-grade-chip{border:1px solid #e4ebf3;border-radius:10px;padding:9px;text-align:center}.ug-grade-chip>span,.ug-grade-chip>strong,.ug-grade-chip>small{display:block}.ug-grade-chip>span{font-size:.75rem;font-weight:800;color:#41617e}.ug-grade-chip>strong{font-size:1rem;color:#1f456c;margin:2px 0}.ug-grade-chip>small{font-size:.62rem;color:#8493a7}.ug-grade-chip.grade-a,.ug-grade-chip.grade-b{background:#f0fbf5}.ug-grade-chip.grade-c,.ug-grade-chip.grade-d,.ug-grade-chip.grade-e{background:#f5f9fd}.ug-grade-chip.grade-f{background:#fff5f5}.ug-grade-chip.grade-f>span,.ug-grade-chip.grade-f>strong{color:#be4d59}.ug-grade-footer{display:flex;gap:14px;flex-wrap:wrap;border-top:1px solid #edf2f7;margin-top:13px;padding-top:10px;color:#71869b;font-size:.7rem}.ug-grade-footer i{color:#3d8ebd;margin-right:4px}.ug-course-grade-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.ug-course-grade-row{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #e7edf3;border-radius:9px;padding:9px 10px}.ug-course-grade-row>span{min-width:0}.ug-course-grade-row strong,.ug-course-grade-row small{display:block}.ug-course-grade-row strong{font-size:.78rem;color:#1765c6}.ug-course-grade-row small{font-size:.67rem;color:#71849a;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-detail-layer{padding-bottom:5px}.ug-detail-layer #resultGroups{margin:0 -16px -5px}.ug-detail-layer .ug-result-level{border-left:0;border-right:0;border-bottom:0;border-radius:0;margin-bottom:0}.ug-detail-layer .ug-result-level:first-child{border-top:1px solid #edf2f7}.ug-detail-layer .ug-result-level summary{padding-left:16px;padding-right:16px}
    @media (max-width:767px){.ug-results-page{padding:9px}.ug-results-context{margin-bottom:9px}.ug-result-layer{padding:12px;border-radius:12px;margin-bottom:9px}.ug-layer-heading{margin-bottom:11px}.ug-layer-heading h2{font-size:.92rem}.ug-layer-heading p{font-size:.67rem}.ug-layer-number{width:25px;height:25px;font-size:.7rem}.ug-layer-total{font-size:.66rem}.ug-grade-chips{grid-template-columns:repeat(3,1fr);gap:6px}.ug-grade-chip{padding:7px 4px}.ug-grade-chip>span{font-size:.7rem}.ug-grade-chip>strong{font-size:.9rem}.ug-grade-chip>small{font-size:.57rem}.ug-grade-footer{gap:8px;font-size:.63rem}.ug-course-grade-list{grid-template-columns:1fr;gap:6px}.ug-course-grade-row{padding:8px 9px}.ug-course-grade-row strong{font-size:.75rem}.ug-course-grade-row small{font-size:.63rem}.ug-detail-layer #resultGroups{margin:0 -12px -5px}.ug-detail-layer .ug-result-level summary{padding-left:12px;padding-right:12px}}
    @media (max-width:767px){.ug-results-filter-bar{display:block;padding:11px 12px;margin-bottom:9px}.ug-results-filter-bar .ug-results-session{margin-top:10px;min-width:0}.ug-session-standing{justify-content:space-between}.ug-results-toolbar-actions{display:block}.ug-results-toolbar-actions .ug-results-search{margin-top:10px}.ug-results-toolbar-actions .ug-results-print{width:100%;margin-top:8px}}
    /* The identity bar keeps the official and calculated CGPA beside the student's name. */
    .ug-results-identity{display:flex;align-items:center;gap:20px;min-width:0;flex:1}.ug-identity-name{min-width:150px}.ug-cgpa-summary{display:flex;align-items:stretch;gap:8px}.ug-cgpa-summary>div{display:flex;align-items:center;gap:8px;border-left:1px solid #e6edf4;padding-left:12px}.ug-cgpa-summary small{display:block;color:#8394a7;font-size:.62rem;line-height:1.2}.ug-cgpa-summary b{display:block;color:#1765c6;font-size:1.05rem;line-height:1}.ug-cgpa-summary>div:first-child b{color:#147a4a}
    /* Distinct, accessible grade colors used consistently in the summary and every course row. */
    .ug-grade-chip.grade-a,.ug-grade.grade-a{background:#e8f7ee;border-color:#bce8ce;color:#137a46}.ug-grade-chip.grade-a>span,.ug-grade-chip.grade-a>strong{color:#137a46}
    .ug-grade-chip.grade-b,.ug-grade.grade-b{background:#e8f0ff;border-color:#c8d9fb;color:#2c5fb7}.ug-grade-chip.grade-b>span,.ug-grade-chip.grade-b>strong{color:#2c5fb7}
    .ug-grade-chip.grade-c,.ug-grade.grade-c{background:#fff4dc;border-color:#f3d99a;color:#a76700}.ug-grade-chip.grade-c>span,.ug-grade-chip.grade-c>strong{color:#a76700}
    .ug-grade-chip.grade-d,.ug-grade.grade-d{background:#f2eaff;border-color:#dcc6f8;color:#7042a8}.ug-grade-chip.grade-d>span,.ug-grade-chip.grade-d>strong{color:#7042a8}
    .ug-grade-chip.grade-e,.ug-grade.grade-e{background:#ffeaf3;border-color:#f5c4d8;color:#b12f68}.ug-grade-chip.grade-e>span,.ug-grade-chip.grade-e>strong{color:#b12f68}
    .ug-grade-chip.grade-f,.ug-grade.grade-f{background:#ffe8eb;border-color:#f2bdc5;color:#b42d3b}.ug-grade-chip.grade-f>span,.ug-grade-chip.grade-f>strong{color:#b42d3b}
    .ug-grade.grade-pending{background:#eef3f8;border-color:#d9e3ec;color:#71839a}
    @media (max-width:767px){.ug-results-identity{display:block}.ug-cgpa-summary{margin-top:10px;gap:6px}.ug-cgpa-summary>div{flex:1;padding-left:9px}.ug-cgpa-summary>div:first-child{border-left:0;padding-left:0}.ug-cgpa-summary small{font-size:.58rem}.ug-cgpa-summary b{font-size:.95rem}.ug-results-filter-bar .ug-results-session{width:100%}}

    .ug-cgpa-summary{display:flex;align-items:stretch;gap:8px}
    .ug-cgpa-card{font:inherit;position:relative;display:flex;align-items:center;gap:8px;min-width:158px;border:1px solid #e2eaf3;border-radius:10px;background:#fbfdff;padding:8px 10px;text-align:left;color:#27496e;cursor:pointer;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
    .ug-cgpa-card:hover,.ug-cgpa-card:focus-visible{border-color:#9bc9e8;box-shadow:0 5px 14px rgba(44,111,192,.12);outline:none;transform:translateY(-1px)}
    .ug-cgpa-card>span{display:block;min-width:0;flex:1}.ug-cgpa-card small,.ug-cgpa-card b{display:block}.ug-cgpa-card small{color:#8394a7;font-size:.61rem;line-height:1.2;white-space:nowrap}.ug-cgpa-card b{color:#1765c6;font-size:1.05rem;line-height:1.15;margin-top:3px}.ug-cgpa-card:first-child b{color:#147a4a}.ug-cgpa-card>i{color:#72a6ca;font-size:.78rem}.ug-cgpa-card em{display:block;position:absolute;left:10px;bottom:-14px;color:#7c95aa;font-size:.54rem;font-style:normal;opacity:0;transition:opacity .18s ease}.ug-cgpa-card:hover em,.ug-cgpa-card:focus-visible em{opacity:1}
    .ug-cgpa-modal[hidden]{display:none}.ug-cgpa-modal{position:fixed;inset:0;z-index:2050;display:flex;align-items:center;justify-content:center;padding:16px}.ug-cgpa-modal-backdrop{position:absolute;inset:0;background:rgba(14,38,63,.58);backdrop-filter:blur(2px)}.ug-cgpa-dialog{position:relative;width:100%;max-width:780px;max-height:90vh;overflow:auto;background:#fff;border:1px solid #dce8f2;border-radius:18px;box-shadow:0 20px 70px rgba(8,38,67,.25);padding:22px}.ug-cgpa-dialog-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;border-bottom:1px solid #edf2f7;padding-bottom:15px}.ug-cgpa-dialog-header h2{margin:5px 0 3px;color:#1d4269;font-size:1.25rem}.ug-cgpa-dialog-header p{margin:0;color:#71859a;font-size:.78rem}.ug-cgpa-close{font:inherit;width:34px;height:34px;border:1px solid #dce8f2;border-radius:10px;background:#f5f9fc;color:#65819a;cursor:pointer;display:grid;place-items:center;flex:0 0 auto}.ug-cgpa-close:hover,.ug-cgpa-close:focus-visible{background:#e9f4fc;color:#1765c6;outline:2px solid #b9dff3;outline-offset:2px}.ug-cgpa-dialog-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:15px 0}.ug-cgpa-dialog-summary>div{border:1px solid #e4edf4;border-radius:11px;background:#f8fbfd;padding:10px}.ug-cgpa-dialog-summary small,.ug-cgpa-dialog-summary strong,.ug-cgpa-dialog-summary span{display:block}.ug-cgpa-dialog-summary small{color:#7b91a6;font-size:.61rem;text-transform:uppercase;letter-spacing:.04em}.ug-cgpa-dialog-summary strong{color:#1765c6;font-size:1.05rem;margin:3px 0}.ug-cgpa-dialog-summary>div:first-child strong{color:#147a4a}.ug-cgpa-dialog-summary span{color:#8b9aaa;font-size:.62rem}.ug-cgpa-formula{border:1px solid #cfe5f5;border-left:4px solid #3ea1e4;border-radius:11px;background:#f1f8fd;padding:11px 13px;margin-bottom:15px}.ug-cgpa-formula span,.ug-cgpa-formula strong,.ug-cgpa-formula p{display:block}.ug-cgpa-formula span{color:#5c7e98;font-size:.64rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}.ug-cgpa-formula strong{color:#1c517b;font-size:.83rem;margin-top:4px}.ug-cgpa-formula p{margin:4px 0 0;color:#1765c6;font-size:.9rem;font-weight:800}.ug-cgpa-breakdown{border:1px solid #e3ebf3;border-radius:12px;overflow:hidden}.ug-cgpa-breakdown-heading{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 13px;background:#fbfdff;border-bottom:1px solid #edf2f7}.ug-cgpa-breakdown-heading strong,.ug-cgpa-breakdown-heading small{display:block}.ug-cgpa-breakdown-heading strong{color:#2c4e70;font-size:.83rem}.ug-cgpa-breakdown-heading small{color:#8495a8;font-size:.67rem;margin-top:2px}.ug-cgpa-breakdown-heading>span{color:#6f8aa1;font-size:.67rem;font-weight:700;white-space:nowrap}.ug-cgpa-table-wrap{overflow-x:auto}.ug-cgpa-table{width:100%;border-collapse:collapse;min-width:540px}.ug-cgpa-table th{padding:8px 10px;background:#eef6fb;color:#57758e;text-align:left;font-size:.65rem;text-transform:uppercase;letter-spacing:.04em}.ug-cgpa-table td{padding:8px 10px;border-top:1px solid #edf2f7;color:#405d78;font-size:.72rem;vertical-align:middle}.ug-cgpa-table td:first-child{min-width:220px}.ug-cgpa-table td strong,.ug-cgpa-table td small{display:block}.ug-cgpa-table td strong{color:#1765c6;font-size:.74rem}.ug-cgpa-table td small{color:#7a8da0;font-size:.65rem;margin-top:2px}.ug-cgpa-grade{display:inline-grid;place-items:center;min-width:27px;border-radius:7px;padding:4px 6px;font-size:.7rem;font-weight:800}.ug-cgpa-grade.grade-a{background:#e8f7ee;color:#137a46}.ug-cgpa-grade.grade-b{background:#e8f0ff;color:#2c5fb7}.ug-cgpa-grade.grade-c{background:#fff4dc;color:#a76700}.ug-cgpa-grade.grade-d{background:#f2eaff;color:#7042a8}.ug-cgpa-grade.grade-e{background:#ffeaf3;color:#b12f68}.ug-cgpa-grade.grade-f{background:#ffe8eb;color:#b42d3b}.ug-cgpa-grade.grade-pending{background:#eef3f8;color:#71839a}.ug-cgpa-no-data{display:flex;align-items:center;gap:8px;padding:18px;color:#7b8da0;font-size:.75rem}.ug-cgpa-no-data i{color:#6a9bc0}.ug-cgpa-note{display:flex;align-items:flex-start;gap:8px;margin-top:13px;padding:10px 12px;border-radius:10px;background:#fff9eb;color:#866d38;font-size:.69rem;line-height:1.45}.ug-cgpa-note i{color:#c49730;margin-top:2px}.ug-cgpa-note span{display:block}.ug-cgpa-modal-open{overflow:hidden}
    @media (max-width:767px){.ug-cgpa-summary{gap:6px;margin-top:11px}.ug-cgpa-card{flex:1;min-width:0;padding:8px}.ug-cgpa-card small{font-size:.57rem;overflow:hidden;text-overflow:ellipsis}.ug-cgpa-card b{font-size:.94rem}.ug-cgpa-card>i{font-size:.7rem}.ug-cgpa-card em{display:none}.ug-cgpa-dialog{max-height:92vh;border-radius:15px;padding:15px}.ug-cgpa-dialog-header h2{font-size:1.05rem}.ug-cgpa-dialog-header p{font-size:.7rem}.ug-cgpa-dialog-summary{grid-template-columns:repeat(2,1fr);gap:6px;margin:12px 0}.ug-cgpa-dialog-summary>div{padding:8px}.ug-cgpa-dialog-summary strong{font-size:.94rem}.ug-cgpa-formula{padding:10px;margin-bottom:11px}.ug-cgpa-formula strong{font-size:.75rem}.ug-cgpa-formula p{font-size:.82rem}.ug-cgpa-breakdown-heading{padding:10px}.ug-cgpa-breakdown-heading small{font-size:.61rem}.ug-cgpa-note{font-size:.65rem}}

    .ug-cgpa-unavailable{color:#a76600!important;font-size:.78rem!important;font-weight:700}.ug-cgpa-table .ug-cgpa-included,.ug-cgpa-table .ug-cgpa-excluded{display:inline-flex;align-items:center;gap:4px;border-radius:7px;padding:4px 6px;font-size:.62rem;font-weight:800;white-space:nowrap}.ug-cgpa-table .ug-cgpa-included{background:#e8f7ee;color:#137a46}.ug-cgpa-table .ug-cgpa-excluded{background:#fff4dc;color:#a76600}.ug-cgpa-table .ug-cgpa-excluded i{font-size:.6rem}.ug-cgpa-table .ug-cgpa-resit-row td{background:#fffaf0}.ug-cgpa-resit-notice{display:flex;align-items:flex-start;gap:8px;margin-top:8px;padding:9px 11px;border:1px solid #f0d79d;border-radius:10px;background:#fff9eb;color:#866d38;font-size:.68rem;line-height:1.4}.ug-cgpa-resit-notice i{color:#c49730;margin-top:2px}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('resultSearch');
        const cards = Array.from(document.querySelectorAll('[data-result-card]'));
        const compactRows = Array.from(document.querySelectorAll('[data-course-grade]'));
        const noMatch = document.getElementById('noResultMatch');
        search?.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            let visible = 0;
            cards.forEach(card => {
                const match = !query || (card.dataset.searchText || '').includes(query);
                card.hidden = !match;
                if (match) visible++;
            });
            compactRows.forEach(row => {
                row.hidden = !!query && !(row.dataset.searchText || '').includes(query);
            });
            document.querySelectorAll('.ug-result-semester').forEach(section => section.hidden = !section.querySelector('[data-result-card]:not([hidden])'));
            document.querySelectorAll('.ug-result-level').forEach(level => level.hidden = !level.querySelector('[data-result-card]:not([hidden])'));
            if (noMatch) noMatch.hidden = visible > 0;
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('cgpaExplanation');
        if (!modal) return;
        const triggers = Array.from(document.querySelectorAll('[data-cgpa-open]'));
        const title = document.getElementById('cgpaExplanationTitle');
        const description = document.getElementById('cgpaExplanationDescription');
        const closeButtons = Array.from(modal.querySelectorAll('[data-cgpa-close]'));
        let lastTrigger = null;

        const closeModal = function () {
            modal.hidden = true;
            document.body.classList.remove('ug-cgpa-modal-open');
            lastTrigger?.focus();
        };

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                lastTrigger = trigger;
                const mode = trigger.dataset.cgpaOpen;
                const metric = modal.dataset.cgpaScope === 'cgpa' ? 'CGPA' : 'GPA';
                if (mode === 'recorded') {
                    title.textContent = 'Your recorded ' + metric;
                    description.textContent = 'This is the official ' + metric + ' saved in session history. The course breakdown below shows the approved-result calculation for comparison.';
                } else {
                    title.textContent = 'How your ' + metric + ' was calculated';
                    description.textContent = 'The calculation uses the approved results currently selected on this page.';
                }
                modal.hidden = false;
                document.body.classList.add('ug-cgpa-modal-open');
                modal.querySelector('.ug-cgpa-close')?.focus();
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    });
</script>