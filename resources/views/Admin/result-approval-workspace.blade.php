@php
    $scopeLabel = $filters['session'] === 'all' ? 'All academic sessions' : $filters['session'];
    $totalBatches = (int) $counts->sum();
    $awaitingBatches = (int) $counts->get($reviewStage, 0);
    $approvedBatches = (int) $counts->get('vc', 0);
    $hasFilters = collect($filters)->except(['page', 'session', 'stage'])->filter()->isNotEmpty();
    $filterLink = function ($stage) use ($filters) {
        return url('/approve results') . '?' . http_build_query(array_merge($filters, ['stage' => $stage, 'page' => 1]));
    };
    $approvalController = app(\App\Http\Controllers\ApproveResultsController::class);
@endphp
<link rel="stylesheet" href="{{ asset('css/result-approval.css') }}?v={{ filemtime(public_path('css/result-approval.css')) }}">
<div id="result-approval" class="ra-workspace">
    <header class="ra-heading">
        <div><div class="ra-kicker">ACADEMIC AFFAIRS <span>/</span> RESULTS</div><h1>{{ $final ? 'Results for final approval' : 'Result approval' }}</h1><p>{{ $final ? 'Review submitted results and complete department approvals.' : 'Review course results and follow their progress through approval.' }}</p></div>
        <div class="ra-session"><i class="far fa-calendar-alt" aria-hidden="true"></i><span><small>Viewing</small><strong>{{ $scopeLabel }}</strong></span></div>
    </header>
    @if (session('success'))
        <div class="ra-notice ra-notice-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ra-notice ra-notice-error" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="ra-notice ra-notice-error" role="alert"><strong>Please check your selections.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <section class="ra-metrics" aria-label="Course approval summary">
        <a href="{{ $filterLink($reviewStage) }}" class="ra-metric ra-metric-focus"><div><span>{{ $final ? 'Awaiting final approval' : 'At your review stage' }}</span><strong>{{ number_format($awaitingBatches) }}</strong><small>{{ $stageLabels[$reviewStage] }} · course batches</small></div><i class="fas fa-file-signature" aria-hidden="true"></i></a>
        <a href="{{ $filterLink('vc') }}" class="ra-metric"><div><span>Approved</span><strong>{{ number_format($approvedBatches) }}</strong><small>Course batches completed</small></div><i class="far fa-check-circle" aria-hidden="true"></i></a>
        <a href="{{ $filterLink('') }}" class="ra-metric"><div><span>All course batches in this view</span><strong>{{ number_format($totalBatches) }}</strong><small>Across all approval stages</small></div><i class="far fa-copy" aria-hidden="true"></i></a>
    </section>
    <div class="ra-layout {{ $final ? 'ra-with-departments' : '' }}">
        @if ($final)
            <aside class="ra-panel ra-departments" aria-label="Departments awaiting final approval">
                <div class="ra-panel-heading"><div><span class="ra-overline">FINAL REVIEW</span><h2>Departments</h2></div><span class="ra-count">{{ $pendingDepartments->count() }}</span></div>
                <p class="ra-side-copy">Course batches awaiting your decision.</p>
                <div class="ra-side-search"><i class="fas fa-search" aria-hidden="true"></i><input id="ra-department-search" type="search" aria-label="Find a department" placeholder="Find a department"></div>
                <div class="ra-department-list">
                    @forelse ($pendingDepartments as $department)
                        <button type="button" class="ra-department" data-department-code="{{ $department->code }}" data-department-title="{{ $department->title }}" data-total="{{ $department->total }}"><span class="ra-department-symbol"><i class="fas fa-university" aria-hidden="true"></i></span><span><strong>{{ $department->title }}</strong><small>{{ number_format($department->total) }} course batches</small></span><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                    @empty
                        <div class="ra-side-empty"><i class="far fa-check-circle" aria-hidden="true"></i><strong>No departments awaiting approval</strong><p>No final approvals match the session and academic area selected.</p></div>
                    @endforelse
                    <p id="ra-no-department" class="ra-side-copy" hidden>No department matches your search.</p>
                </div>
                <div class="ra-side-foot"><i class="far fa-calendar-alt" aria-hidden="true"></i> {{ $scopeLabel }}<br><span>Department decisions include every pending course for this session selection.</span></div>
            </aside>
        @endif
        <div class="ra-main"><section class="ra-panel">
            <details class="ra-filters" @if ($hasFilters) open @endif>
                <summary><span><i class="fas fa-sliders-h" aria-hidden="true"></i> Filter results <small>Your selections are remembered</small></span><i class="fas fa-chevron-down ra-filter-chevron" aria-hidden="true"></i></summary>
                <form action="{{ url('/approve results') }}" method="GET" id="ra-filter-form">
                    <div class="ra-filter-grid">
                        <label class="ra-search-field">Course code or title<input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search courses"></label>
                        <label>Academic session<select name="session"><option value="all" {{ $filters['session'] === 'all' ? 'selected' : '' }}>All sessions</option>@foreach ($academicSessions as $academicSession)<option value="{{ $academicSession }}" {{ $filters['session'] === $academicSession ? 'selected' : '' }}>{{ $academicSession }}</option>@endforeach</select></label>
                        <label>Semester<select name="semester"><option value="">All semesters</option>@foreach (['FIRST', 'SECOND', 'THIRD', 'RESIT'] as $semester)<option value="{{ $semester }}" {{ $filters['semester'] === $semester ? 'selected' : '' }}>{{ ucfirst(strtolower($semester)) }}</option>@endforeach</select></label>
                        <label>Faculty / college<select name="faculty" id="ra-faculty"><option value="">All faculties</option>@foreach ($faculties as $faculty)<option value="{{ $faculty->code }}" {{ $filters['faculty'] === $faculty->code ? 'selected' : '' }}>{{ $faculty->title }}</option>@endforeach</select></label>
                        <label>Department<select name="department" id="ra-department"><option value="">All departments</option>@foreach ($departments as $department)<option data-faculty="{{ $department->faculty }}" value="{{ $department->code }}" {{ $filters['department'] === $department->code ? 'selected' : '' }}>{{ $department->title }}</option>@endforeach</select></label>
                        <label>Programme<select name="program" id="ra-program"><option value="">All programmes</option>@foreach ($programs as $programme)<option data-faculty="{{ $programme->faculty }}" data-department="{{ $programme->department }}" value="{{ $programme->code }}" {{ $filters['program'] === $programme->code ? 'selected' : '' }}>{{ $programme->title }}</option>@endforeach</select></label>
                        <label>Approval stage<select name="stage"><option value="">All stages</option>@foreach ($stageLabels as $stage => $label)<option value="{{ $stage }}" {{ $filters['stage'] === $stage ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
                    </div>
                    <div class="ra-filter-footer"><span><i class="far fa-bookmark" aria-hidden="true"></i> Return to the same view after reviewing a course.</span><div><a href="{{ url('/approve results?reset=1') }}" class="ra-button ra-button-light">Reset</a><button type="submit" class="ra-button ra-button-primary"><i class="fas fa-search" aria-hidden="true"></i> Apply filters</button></div></div>
                </form>
            </details>
            <div class="ra-list-heading"><div><h2>Course results <span class="ra-count">{{ number_format($rows->total()) }}</span></h2><p>{{ $scopeLabel }}@if ($filters['search']) · “{{ $filters['search'] }}”@endif</p></div><span class="ra-list-hint">25 per page</span></div>
            <nav class="ra-tabs" aria-label="Result review stages">
                <a class="{{ $filters['stage'] === $reviewStage ? 'is-active' : '' }}" href="{{ $filterLink($reviewStage) }}" @if ($filters['stage'] === $reviewStage) aria-current="page" @endif>{{ $final ? 'Awaiting approval' : 'At my stage' }} <span>{{ number_format($awaitingBatches) }}</span></a>
                <a class="{{ $filters['stage'] === '' ? 'is-active' : '' }}" href="{{ $filterLink('') }}" @if ($filters['stage'] === '') aria-current="page" @endif>All stages <span>{{ number_format($totalBatches) }}</span></a>
                <a class="{{ $filters['stage'] === 'vc' ? 'is-active' : '' }}" href="{{ $filterLink('vc') }}" @if ($filters['stage'] === 'vc') aria-current="page" @endif>Approved <span>{{ number_format($approvedBatches) }}</span></a>
            </nav>
            <div class="ra-table-wrap"><table class="ra-table">
                <thead><tr><th scope="col">Course</th><th scope="col">Session / semester</th><th scope="col">Approval stage</th><th scope="col" class="ra-action-heading">Actions</th></tr></thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $context = ['code' => $row->code, 'session' => $row->session, 'semester' => $row->semester, 'approve' => $row->approve];
                        $canSubmit = $approvalController->canSubmitApprovalStage($context);
                        $canView = \App\Support\PendingResultAccess::canView($row->code, $row->session);
                        $canAdjust = $canSubmit && strtoupper(trim((string) $row->lecturer)) === strtoupper(trim((string) session('username'))) && \App\Support\PortalPermission::allows('/approve results', 'update');
                        $reviewUrl = url('/pending-results') . '?' . http_build_query($context);
                    @endphp
                    <tr>
                        <td class="ra-course-cell"><strong>{{ $row->code }}</strong><span>{{ $titles->get($row->code, 'Course results') }}</span>@if ($row->updated_at)<small>Updated {{ \Carbon\Carbon::parse($row->updated_at)->format('d M Y') }}</small>@endif</td>
                        <td data-label="Session"><strong>{{ $row->session }}</strong><span>{{ ucfirst(strtolower($row->semester)) }} semester</span></td>
                        <td data-label="Stage"><span class="ra-stage ra-stage-{{ $row->approve }}"><i class="fas {{ $row->approve === 'vc' ? 'fa-check-circle' : 'fa-circle' }}" aria-hidden="true"></i>{{ $stageLabels[$row->approve] ?? 'Under review' }}</span></td>
                        <td class="ra-row-actions">
                            @if ($canView)
                                <a class="ra-button {{ $canSubmit ? 'ra-button-primary' : 'ra-button-light' }}" href="{{ $reviewUrl }}">{{ $canSubmit ? 'Review' : 'View' }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                <a class="ra-icon-button" title="Download {{ $row->code }} result sheet" aria-label="Download {{ $row->code }} result sheet" href="{{ url('/print-result-pdf2/' . $row->code . '/' . $row->session . '/' . $row->approve . '/' . $row->semester) }}" target="_blank" rel="noopener"><i class="fas fa-download" aria-hidden="true"></i></a>
                            @endif
                            <button type="button" class="ra-icon-button" data-comment="{{ $row->comment ?: 'No remarks have been recorded for this course.' }}" data-course="{{ $row->code }}" title="View remarks" aria-label="View remarks for {{ $row->code }}"><i class="far fa-comment-alt" aria-hidden="true"></i></button>
                            @if ($canAdjust)<button type="button" class="ra-icon-button" data-adjust="{{ json_encode($context) }}" title="Adjust marks" aria-label="Adjust marks for {{ $row->code }}"><i class="fas fa-plus" aria-hidden="true"></i></button>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="ra-empty"><span><i class="far fa-folder-open" aria-hidden="true"></i></span><h3>No course results in this view</h3><p>Choose another approval stage or adjust your academic filters.</p><a class="ra-button ra-button-light" href="{{ url('/approve results?reset=1') }}">Reset filters</a></div></td></tr>
                @endforelse
                </tbody>
            </table></div>
            <footer class="ra-pagination"><span>@if ($rows->total()) Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ number_format($rows->total()) }} course batches @else No results to display @endif</span>{{ $rows->links('pagination::bootstrap-4') }}</footer>
        </section></div>
    </div>
    <dialog id="ra-decision-dialog" class="ra-dialog" aria-labelledby="ra-decision-title">
        <form id="ra-decision-form" action="{{ route('approve-department-results') }}" method="POST">
            @csrf
            <input type="hidden" name="dept" id="ra-decision-dept"><input type="hidden" name="session" value="{{ $filters['session'] }}">
            <div class="ra-dialog-heading"><div><span class="ra-overline">DEPARTMENT DECISION</span><h2 id="ra-decision-title">Final approval</h2></div><button type="button" class="ra-icon-button" data-close-dialog aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button></div>
            <div class="ra-dialog-body">
                <p id="ra-decision-department" class="ra-decision-name"></p>
                <div class="ra-decision-scope"><div><small>Academic session</small><strong>{{ $scopeLabel }}</strong></div><div><small>Awaiting final approval</small><strong id="ra-decision-count"></strong></div></div>
                <p>This decision applies to <strong>every pending course result in this department</strong> for {{ strtolower($scopeLabel) }}. Course and programme filters do not limit a department decision.</p>
                @if ($canApproveDepartment || $canRejectDepartment)
                    <fieldset class="ra-decisions"><legend>Choose your decision</legend>
                        @if ($canApproveDepartment)<label><input type="radio" name="status" value="Approved" required><span><strong>Approve results</strong><small>Complete final approval for the department.</small></span><i class="far fa-check-circle" aria-hidden="true"></i></label>@endif
                        @if ($canRejectDepartment)<label><input type="radio" name="status" value="Rejected" required><span><strong>Return for review</strong><small>Send results back to Course System.</small></span><i class="fas fa-undo" aria-hidden="true"></i></label>@endif
                    </fieldset>
                @else
                    <p>You can review these results. Final decisions are not available for your account.</p>
                @endif
                <div class="ra-progress-area" hidden><div class="ra-progress-track" role="progressbar" aria-label="Approval progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div><p id="ra-progress-text" role="status">Preparing results…</p></div>
                <div id="ra-decision-feedback" class="ra-notice" role="status" hidden></div>
            </div>
            <div class="ra-dialog-footer"><button type="button" class="ra-button ra-button-light" data-close-dialog>Cancel</button>@if ($canApproveDepartment || $canRejectDepartment)<button type="submit" class="ra-button ra-button-primary" id="ra-confirm-decision">Confirm decision</button>@endif<button type="button" class="ra-button ra-button-primary" id="ra-refresh-list" hidden>Return to results</button></div>
        </form>
    </dialog>
    <dialog id="ra-comment-dialog" class="ra-dialog" aria-labelledby="ra-comment-title"><div class="ra-dialog-heading"><h2 id="ra-comment-title">Course remarks</h2><button type="button" class="ra-icon-button" data-close-dialog aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button></div><div class="ra-dialog-body"><p id="ra-comment-text" class="ra-remark-text"></p></div><div class="ra-dialog-footer"><button type="button" class="ra-button ra-button-primary" data-close-dialog>Close</button></div></dialog>
    <dialog id="ra-marks-dialog" class="ra-dialog" aria-labelledby="ra-marks-title"><form action="{{ url('/update-mark') }}" method="POST">@csrf
        <input type="hidden" name="code"><input type="hidden" name="session"><input type="hidden" name="semester">
        <div class="ra-dialog-heading"><h2 id="ra-marks-title">Adjust course marks</h2><button type="button" class="ra-icon-button" data-close-dialog aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button></div>
        <div class="ra-dialog-body"><p id="ra-marks-course"></p><div class="ra-filter-grid ra-marks-fields"><label>Marks to add<input type="number" name="mark" step="0.01" required></label><label>Minimum existing score<input type="number" name="min" step="0.01" required></label><label>Maximum existing score<input type="number" name="max" step="0.01" required></label></div></div>
        <div class="ra-dialog-footer"><button type="button" class="ra-button ra-button-light" data-close-dialog>Cancel</button><button type="submit" class="ra-button ra-button-primary">Save marks</button></div>
    </form></dialog>
</div>
<script src="{{ asset('js/result-approval.js') }}?v={{ filemtime(public_path('js/result-approval.js')) }}" defer></script>
