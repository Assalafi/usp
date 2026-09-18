@php
    $studentResults = collect($data ?? []);
    $resultsByLevel = $studentResults->groupBy('level');
    $courseCount = $studentResults->count();
    $unitCount = $studentResults->sum(fn ($result) => (float) ($result->unit ?? 0));
    $failedCount = $studentResults->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === 'F')->count();
    $passedCount = $studentResults->filter(fn ($result) => !empty($result->grade) && strtoupper((string) $result->grade) !== 'F')->count();
    $gradeOrder = ['A', 'B', 'C', 'D', 'E', 'F'];
    $gradeCounts = collect($gradeOrder)->mapWithKeys(fn ($grade) => [$grade => $studentResults->filter(fn ($result) => strtoupper((string) ($result->grade ?? '')) === $grade)->count()]);
    $studentName = $student->fullname ?? session('fullname') ?? session('id_number');
    $displaySession = $selectedSession ?? request('session', session('system_session'));
    $isAllSessions = strtolower((string) $displaySession) === 'all';
    $displaySessionLabel = $isAllSessions ? 'All sessions' : $displaySession;
@endphp

<div class="main-body ug-results-page">
    <div class="page-wrapper">
        <div class="ug-results-shell">
            <section class="ug-results-hero">
                <div class="ug-results-hero-copy">
                    <span class="ug-results-icon"><i class="fas fa-chart-line"></i></span>
                    <div>
                        <span class="ug-results-kicker">Academic record</span>
                        <h1>My results</h1>
                        <p>Review the approved results published for your academic session.</p>
                    </div>
                </div>
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
            </section>

            <section class="ug-result-layer ug-grade-layer" aria-labelledby="gradeSummaryTitle">
                <div class="ug-layer-heading"><div><span class="ug-layer-number">1</span><div><h2 id="gradeSummaryTitle">Grade summary</h2><p>A quick view of your grades for {{ strtolower($displaySessionLabel) }}.</p></div></div><span class="ug-layer-total">{{ $courseCount }} courses</span></div>
                <div class="ug-grade-chips">
                    @foreach ($gradeOrder as $grade)
                        <div class="ug-grade-chip grade-{{ strtolower($grade) }}"><span>{{ $grade }}</span><strong>{{ $gradeCounts[$grade] }}</strong><small>{{ $grade === 'F' ? 'Carryover' : 'Grade' }}</small></div>
                    @endforeach
                </div>
                <div class="ug-grade-footer"><span><i class="fas fa-layer-group"></i> {{ $unitCount }} total units</span><span><i class="fas fa-circle-check"></i> {{ $passedCount }} passed</span><span><i class="fas fa-circle-exclamation"></i> {{ $failedCount }} carryover</span></div>
            </section>

            <section class="ug-results-context">
                <div>
                    <span class="ug-results-kicker">{{ $displaySessionLabel }}</span>
                    <h2>{{ $studentName }}</h2>
                    <p>{{ session('id_number') }} <span aria-hidden="true">•</span> Approved academic results</p>
                </div>
                <button type="button" class="ug-results-print" onclick="window.print()"><i class="fas fa-print"></i><span>Print results</span></button>
            </section>

            @if ($studentResults->isNotEmpty())
                <section class="ug-result-layer ug-course-grade-layer" aria-labelledby="courseGradesTitle">
                    <div class="ug-layer-heading"><div><span class="ug-layer-number">2</span><div><h2 id="courseGradesTitle">Course grades</h2><p>Each course and its final grade.</p></div></div><span class="ug-layer-total">{{ $courseCount }} results</span></div>
                    <div class="ug-course-grade-list" id="courseGradeList">
                        @foreach ($studentResults as $result)
                            @php $compactGrade = strtoupper((string) ($result->grade ?? '')); $compactTone = $compactGrade === 'F' ? 'fail' : (in_array($compactGrade, ['A', 'B', 'C', 'D', 'E']) ? 'pass' : 'pending'); @endphp
                            <div class="ug-course-grade-row" data-course-grade data-search-text="{{ strtolower(($result->code ?? '') . ' ' . ($result->title ?? '')) }}"><span><strong>{{ $result->code }}</strong><small>{{ $result->title ?? 'Course result' }}{{ $isAllSessions && !empty($result->session) ? ' · ' . $result->session : '' }}</small></span><b class="ug-grade {{ $compactTone }}">{{ $compactGrade ?: '—' }}</b></div>
                        @endforeach
                    </div>
                </section>
                <section class="ug-result-layer ug-detail-layer" aria-labelledby="detailTitle">
                    <div class="ug-layer-heading"><div><span class="ug-layer-number">3</span><div><h2 id="detailTitle">Detailed results</h2><p>Open a level to view the complete result breakdown.</p></div></div></div>
                    <div class="ug-results-toolbar">
                        <div><strong>Detailed result structure</strong><span>Marks, total, units, semester and approval status</span></div>
                        <label class="ug-results-search"><i class="fas fa-search"></i><input id="resultSearch" type="search" placeholder="Search results" autocomplete="off"></label>
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
                                                    $searchText = strtolower(($result->code ?? '') . ' ' . ($result->title ?? ''));
                                                @endphp
                                                <article class="ug-result-card" data-result-card data-search-text="{{ $searchText }}">
                                                    <div class="ug-result-card-top"><div><strong class="ug-result-code">{{ $result->code }}</strong><span class="ug-result-title">{{ $result->title ?? 'Course result' }}</span></div><span class="ug-grade {{ $gradeTone }}">{{ $grade ?: '—' }}</span></div>
                                                    <div class="ug-result-marks"><div><small>CA</small><strong>{{ $result->ca ?? '—' }}</strong></div><div><small>Exam</small><strong>{{ $result->exam ?? '—' }}</strong></div><div class="total"><small>Total</small><strong>{{ $result->total ?? '—' }}</strong></div></div>
                                                    <div class="ug-result-meta"><span><i class="fas fa-cubes"></i> {{ $result->unit ?? '—' }} unit{{ (float) ($result->unit ?? 0) === 1.0 ? '' : 's' }}</span><span><i class="fas fa-calendar"></i> {{ $semester ?: 'Semester not specified' }}</span>@if ($isAllSessions && !empty($result->session))<span><i class="fas fa-clock"></i> {{ $result->session }}</span>@endif<span class="ug-approved"><i class="fas fa-shield-check"></i> Approved</span></div>
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
        </div>
    </div>
</div>

<style>
    .ug-results-page{background:#f4f7fb;min-height:calc(100vh - 70px);padding:clamp(12px,2vw,28px)}.ug-results-shell{max-width:1180px;margin:0 auto}.ug-results-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;background:#fff;border:1px solid #e1eaf3;border-left:5px solid #3ea1e4;border-radius:18px;padding:clamp(20px,3vw,30px);box-shadow:0 8px 24px rgba(29,61,103,.06)}.ug-results-hero-copy{display:flex;align-items:center;gap:15px}.ug-results-icon{width:50px;height:50px;display:grid;place-items:center;border-radius:14px;background:#eaf5fd;color:#258ac8;font-size:1.35rem;flex:0 0 auto}.ug-results-kicker{display:block;color:#39769b;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.ug-results-hero h1{color:#183b64;font-size:clamp(1.65rem,3vw,2.25rem);margin:6px 0 4px;font-weight:800}.ug-results-hero p{color:#6c8097;margin:0;font-size:.9rem}.ug-results-session{background:#f4f9fe;border:1px solid #dce9f5;border-radius:12px;padding:10px 12px;min-width:210px}.ug-results-session label{display:block;color:#55738f;font-size:.7rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}.ug-results-session select{width:100%;background:#fff;color:#24578e;border:1px solid #cfe0ee;border-radius:8px;font-weight:700;padding:8px 9px}
    .ug-results-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0}.ug-result-stat{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e4ebf3;border-radius:13px;padding:13px;min-width:0}.ug-result-stat strong,.ug-result-stat small{display:block}.ug-result-stat strong{color:#19365e;font-size:1.2rem}.ug-result-stat small{color:#76879c;font-size:.72rem;margin-top:2px}.ug-result-stat-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;flex:0 0 auto}.ug-result-stat-icon.blue{background:#e8f1ff;color:#2765cb}.ug-result-stat-icon.cyan{background:#e7f8fc;color:#1a91ab}.ug-result-stat-icon.green{background:#e7f8ef;color:#16834b}.ug-result-stat-icon.red{background:#fff0f0;color:#c24b58}
    .ug-results-context{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#173a62;color:#fff;border-radius:15px;padding:18px 20px;margin-bottom:14px}.ug-results-context .ug-results-kicker{color:#a9d5ef}.ug-results-context h2{margin:4px 0;color:#fff;font-size:1.15rem}.ug-results-context p{margin:0;color:#c7d9e9;font-size:.78rem}.ug-results-print{border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);color:#fff;border-radius:9px;padding:9px 12px;font-weight:700;cursor:pointer;white-space:nowrap}.ug-results-print:hover{background:rgba(255,255,255,.18)}.ug-results-print i{margin-right:6px}.ug-results-toolbar{display:flex;justify-content:space-between;align-items:center;gap:14px;background:#fff;border:1px solid #e3ebf3;border-radius:13px;padding:13px 15px;margin-bottom:12px}.ug-results-toolbar strong,.ug-results-toolbar span{display:block}.ug-results-toolbar strong{color:#27496e;font-size:.95rem}.ug-results-toolbar span{color:#8493a7;font-size:.72rem;margin-top:2px}.ug-results-search{display:flex;align-items:center;gap:8px;border:1px solid #d8e3ee;border-radius:9px;background:#f9fbfd;padding:7px 10px;color:#7b8da2;min-width:220px}.ug-results-search input{border:0;background:transparent;outline:0;width:100%;font-size:.8rem;color:#29496b}.ug-result-level{background:#fff;border:1px solid #e2eaf3;border-radius:14px;margin-bottom:11px;overflow:hidden}.ug-result-level summary{display:flex;align-items:center;gap:11px;list-style:none;cursor:pointer;padding:14px 16px}.ug-result-level summary::-webkit-details-marker{display:none}.ug-result-level summary>span:nth-child(2){display:flex;flex-direction:column;flex:1}.ug-result-level summary strong{color:#27496e;font-size:.9rem}.ug-result-level summary small{color:#8391a5;font-size:.72rem;margin-top:2px}.ug-result-level summary>i{color:#7e91a7;font-size:.78rem;transition:transform .2s}.ug-result-level[open] summary>i{transform:rotate(180deg)}.ug-result-level-badge{width:35px;height:35px;display:grid;place-items:center;border-radius:10px;background:#eaf4ff;color:#1765c6;font-weight:800}.ug-result-level-body{border-top:1px solid #edf2f7;padding:14px 16px}.ug-result-semester{margin-bottom:18px}.ug-result-semester:last-child{margin-bottom:0}.ug-result-semester-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:9px;color:#426487;font-size:.78rem;font-weight:800}.ug-result-semester-heading b{font-size:.7rem;color:#8a98a9;font-weight:600}.ug-result-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ug-result-card{border:1px solid #e4ebf3;border-radius:12px;padding:13px;background:#fff}.ug-result-card:hover{border-color:#abd1ed;box-shadow:0 3px 12px rgba(44,111,192,.06)}.ug-result-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.ug-result-card-top>div{min-width:0}.ug-result-code,.ug-result-title{display:block}.ug-result-code{color:#1765c6;font-size:.86rem}.ug-result-title{color:#4a5d73;font-size:.78rem;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-grade{min-width:34px;text-align:center;border-radius:8px;padding:6px 8px;font-weight:800;font-size:.8rem}.ug-grade.pass{background:#e7f8ef;color:#16834b}.ug-grade.fail{background:#fff0f0;color:#c24b58}.ug-grade.pending{background:#eef3f8;color:#71839a}.ug-result-marks{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;border-top:1px solid #edf2f7;border-bottom:1px solid #edf2f7;margin:12px 0 9px;padding:9px 0}.ug-result-marks div{text-align:center}.ug-result-marks small,.ug-result-marks strong{display:block}.ug-result-marks small{color:#8594a7;font-size:.65rem}.ug-result-marks strong{color:#304d6c;font-size:.9rem;margin-top:2px}.ug-result-marks .total{background:#f1f7fc;border-radius:7px;padding:4px}.ug-result-marks .total strong{color:#1765c6}.ug-result-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;color:#8492a4;font-size:.68rem}.ug-result-meta i{color:#6e9bc1;margin-right:3px}.ug-result-meta .ug-approved{color:#16834b;font-weight:700}.ug-result-meta .ug-approved i{color:#16834b}.ug-results-empty{display:flex;align-items:center;justify-content:center;gap:12px;background:#fff;border:1px solid #e4ebf3;border-radius:14px;padding:20px;color:#7b8ca0}.ug-results-empty>i{font-size:1.3rem;color:#8aa5bd}.ug-results-empty strong,.ug-results-empty small{display:block}.ug-results-empty strong{color:#47617e;font-size:.85rem}.ug-results-empty small{font-size:.75rem;margin-top:3px}.ug-results-empty-large{display:block;text-align:center;padding:58px 18px}.ug-results-empty-large>span{width:58px;height:58px;display:grid;place-items:center;border-radius:50%;background:#eaf4ff;color:#3981bd;font-size:1.45rem;margin:0 auto 13px}.ug-results-empty-large h3{color:#2d4a6b;font-size:1.1rem;margin:0 0 6px}.ug-results-empty-large p{color:#7c8ca0;font-size:.82rem;max-width:440px;margin:0 auto}
    @media (max-width:767px){.ug-results-page{padding:10px;background:#f6f8fb}.ug-results-hero{display:block;border-radius:15px;padding:19px 15px}.ug-results-hero-copy{align-items:flex-start;gap:11px}.ug-results-icon{width:40px;height:40px;font-size:1.05rem}.ug-results-hero h1{font-size:1.55rem}.ug-results-hero p{font-size:.8rem}.ug-results-session{margin-top:16px;min-width:0}.ug-results-summary{grid-template-columns:repeat(2,1fr);gap:8px;margin:10px 0}.ug-result-stat{padding:10px;gap:8px}.ug-result-stat-icon{width:32px;height:32px;font-size:.85rem}.ug-result-stat strong{font-size:1rem}.ug-result-stat small{font-size:.67rem}.ug-results-context{display:block;border-radius:13px;padding:15px;margin-bottom:10px}.ug-results-context h2{font-size:1rem}.ug-results-context p{font-size:.7rem}.ug-results-print{margin-top:12px;width:100%;padding:8px}.ug-results-toolbar{display:block;padding:12px;margin-bottom:9px}.ug-results-search{margin-top:10px;min-width:0;width:100%}.ug-result-level summary{padding:12px}.ug-result-level-body{padding:11px 10px}.ug-result-cards{grid-template-columns:1fr}.ug-result-card{padding:12px}.ug-result-meta{gap:7px}.ug-result-semester-heading{font-size:.74rem}.ug-result-semester-heading b{font-size:.66rem}}
    @media print{.ug-results-page{background:#fff;padding:0}.ug-results-hero,.ug-results-summary,.ug-results-toolbar,.ug-results-print,.ug-results-session{box-shadow:none}.ug-results-hero{border:1px solid #ccc}.ug-results-print,.ug-results-search{display:none}.ug-result-level{break-inside:avoid}.pcoded-navbar,.pcoded-header,.loader-bg{display:none!important}.ug-results-page{margin:0!important}.ug-result-card:hover{box-shadow:none}}
    .ug-result-layer{background:#fff;border:1px solid #e2eaf3;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 5px 16px rgba(29,61,103,.04)}.ug-layer-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.ug-layer-heading>div{display:flex;align-items:center;gap:10px}.ug-layer-number{width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#eaf4ff;color:#1765c6;font-size:.78rem;font-weight:800;flex:0 0 auto}.ug-layer-heading h2{margin:0;color:#27496e;font-size:1rem}.ug-layer-heading p{margin:3px 0 0;color:#8493a7;font-size:.72rem}.ug-layer-total{color:#7890a7;font-size:.72rem;font-weight:700;white-space:nowrap}.ug-grade-chips{display:grid;grid-template-columns:repeat(6,1fr);gap:8px}.ug-grade-chip{border:1px solid #e4ebf3;border-radius:10px;padding:9px;text-align:center}.ug-grade-chip>span,.ug-grade-chip>strong,.ug-grade-chip>small{display:block}.ug-grade-chip>span{font-size:.75rem;font-weight:800;color:#41617e}.ug-grade-chip>strong{font-size:1rem;color:#1f456c;margin:2px 0}.ug-grade-chip>small{font-size:.62rem;color:#8493a7}.ug-grade-chip.grade-a,.ug-grade-chip.grade-b{background:#f0fbf5}.ug-grade-chip.grade-c,.ug-grade-chip.grade-d,.ug-grade-chip.grade-e{background:#f5f9fd}.ug-grade-chip.grade-f{background:#fff5f5}.ug-grade-chip.grade-f>span,.ug-grade-chip.grade-f>strong{color:#be4d59}.ug-grade-footer{display:flex;gap:14px;flex-wrap:wrap;border-top:1px solid #edf2f7;margin-top:13px;padding-top:10px;color:#71869b;font-size:.7rem}.ug-grade-footer i{color:#3d8ebd;margin-right:4px}.ug-course-grade-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.ug-course-grade-row{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #e7edf3;border-radius:9px;padding:9px 10px}.ug-course-grade-row>span{min-width:0}.ug-course-grade-row strong,.ug-course-grade-row small{display:block}.ug-course-grade-row strong{font-size:.78rem;color:#1765c6}.ug-course-grade-row small{font-size:.67rem;color:#71849a;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-detail-layer{padding-bottom:5px}.ug-detail-layer #resultGroups{margin:0 -16px -5px}.ug-detail-layer .ug-result-level{border-left:0;border-right:0;border-bottom:0;border-radius:0;margin-bottom:0}.ug-detail-layer .ug-result-level:first-child{border-top:1px solid #edf2f7}.ug-detail-layer .ug-result-level summary{padding-left:16px;padding-right:16px}
    @media (max-width:767px){.ug-results-page{padding:9px}.ug-results-context{margin-bottom:9px}.ug-result-layer{padding:12px;border-radius:12px;margin-bottom:9px}.ug-layer-heading{margin-bottom:11px}.ug-layer-heading h2{font-size:.92rem}.ug-layer-heading p{font-size:.67rem}.ug-layer-number{width:25px;height:25px;font-size:.7rem}.ug-layer-total{font-size:.66rem}.ug-grade-chips{grid-template-columns:repeat(3,1fr);gap:6px}.ug-grade-chip{padding:7px 4px}.ug-grade-chip>span{font-size:.7rem}.ug-grade-chip>strong{font-size:.9rem}.ug-grade-chip>small{font-size:.57rem}.ug-grade-footer{gap:8px;font-size:.63rem}.ug-course-grade-list{grid-template-columns:1fr;gap:6px}.ug-course-grade-row{padding:8px 9px}.ug-course-grade-row strong{font-size:.75rem}.ug-course-grade-row small{font-size:.63rem}.ug-detail-layer #resultGroups{margin:0 -12px -5px}.ug-detail-layer .ug-result-level summary{padding-left:12px;padding-right:12px}}
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
