@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    $student = DB::table('students')->where('username', session('id_number'))->first();
    if (!$student && session('id')) {
        $student = DB::table('students')->where('user_id', session('id'))->first();
    }

    $studentName = trim((string) ($student->fullname ?? session('username') ?? 'Student'));
    $studentId = $student->username ?? session('id_number');
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $programCode = $student->program ?? session('program');
    $programTitle = $programCode ? DB::table('program')->where('code', $programCode)->value('title') : null;
    $level = $student->level ?? session('current_level') ?? session('level');

    $registeredCourses = DB::table('student_course_registration')
        ->where('username', $studentId)
        ->where('session', $currentSession)
        ->get();
    $registeredCount = $registeredCourses->count();
    $registeredUnits = (float) $registeredCourses->sum(function ($course) {
        return (float) ($course->unit ?? $course->units ?? 0);
    });
    $registrationSemesters = $registeredCourses->pluck('semester')->filter()->unique()->values();

    $sessionResults = DB::table('results')
        ->where('username', $studentId)
        ->where('session', $currentSession)
        ->where('approve', 'vc')
        ->orderByDesc('updated_at')
        ->get();
    $resultCourses = $sessionResults->pluck('code')->filter()->unique()->count();
    $lastResultUpdate = $sessionResults->first()->updated_at ?? null;
    $history = DB::table('session_history')->where(['username' => $studentId, 'session' => $currentSession])->first();
    $academicStatus = $history->status ?? 'In progress';

    $invoiceUsernames = collect([session('id'), $studentId])->filter()->unique()->values()->all();
    $invoices = DB::table('invoices')->whereIn('username', $invoiceUsernames)->where('session', $currentSession)->get();
    $paidAmount = (float) $invoices->where('status', 'Paid')->sum('amount');
    $pendingAmount = (float) $invoices->where('status', 'Pending')->sum('amount');
    $pendingPayments = $invoices->where('status', 'Pending')->count();

    $photo = $student->picture ?? null;
    $photoUrl = $photo ? asset('storage/picture/' . $photo) : asset('storage/picture/default.jpg');
    $initials = collect(preg_split('/\s+/', $studentName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $formatMoney = fn ($amount) => '₦' . number_format((float) $amount, 2);
@endphp

<div class="main-body ug-dashboard-page">
    <div class="page-wrapper">
        <section class="ug-welcome-card">
            <div class="ug-welcome-copy">
                <span class="ug-eyebrow"><i class="fas fa-star"></i> Student portal</span>
                <h1>Welcome back, {{ Str::before($studentName, ' ') ?: $studentName }}.</h1>
                <p>Stay on top of your {{ $currentSession ?: 'current' }} academic activities.</p>
                <div class="ug-meta-row">
                    <span><i class="fas fa-id-card"></i> {{ $studentId ?: 'Matric number not set' }}</span>
                    <span><i class="fas fa-calendar-alt"></i> {{ $currentSession ?: 'Session unavailable' }}</span>
                </div>
            </div>
            <div class="ug-avatar-wrap">
                @if ($photo)
                    <img src="{{ $photoUrl }}" alt="{{ $studentName }}" class="ug-avatar">
                @else
                    <div class="ug-avatar ug-avatar-initials">{{ $initials ?: 'ST' }}</div>
                @endif
                <span class="ug-online-dot" title="Active student"></span>
            </div>
            <div class="ug-welcome-academic">
                <div><span>Current level</span><strong>{{ $level ? $level . ' Level' : 'Not set' }}</strong></div>
                <div><span>Course of study</span><strong>{{ $programTitle ?: $programCode ?: 'Not set' }}</strong></div>
                <a href="{{ url('/profile') }}" class="ug-welcome-profile"><i class="fas fa-user-edit"></i> View profile</a>
            </div>
        </section>

        <section class="ug-stat-grid" aria-label="Academic summary">
            <a class="ug-stat-card ug-stat-blue" href="{{ url('/student course registration') }}">
                <span class="ug-stat-icon"><i class="fas fa-book-open"></i></span>
                <span class="ug-stat-label">Registered courses</span>
                <strong>{{ $registeredCount }}</strong>
                <small>{{ number_format($registeredUnits, 1) }} credit units</small>
            </a>
            <a class="ug-stat-card ug-stat-purple" href="{{ url('/student-result') }}">
                <span class="ug-stat-icon"><i class="fas fa-chart-line"></i></span>
                <span class="ug-stat-label">Current results</span>
                <strong>{{ $resultCourses }}</strong>
                <small>{{ $resultCourses ? 'Approved · current session' : 'Awaiting publication' }}</small>
            </a>
            <a class="ug-stat-card ug-stat-green" href="{{ url('/payment') }}">
                <span class="ug-stat-icon"><i class="fas fa-wallet"></i></span>
                <span class="ug-stat-label">Paid this session</span>
                <strong>{{ $formatMoney($paidAmount) }}</strong>
                <small>{{ $pendingPayments ? $pendingPayments . ' pending payment(s)' : 'All payments up to date' }}</small>
            </a>
            <div class="ug-stat-card ug-stat-orange">
                <span class="ug-stat-icon"><i class="fas fa-graduation-cap"></i></span>
                <span class="ug-stat-label">Academic standing</span>
                <strong>{{ ucfirst((string) $academicStatus) }}</strong>
                <small>Current session status</small>
            </div>
        </section>

        <div class="ug-content-grid">
            <section class="ug-panel ug-activity-panel">
                <div class="ug-panel-heading"><div><span class="ug-kicker">{{ $currentSession ?: 'Current session' }}</span><h2>What’s happening</h2></div></div>
                <div class="ug-activity-list">
                    <a href="{{ url('/student course registration') }}" class="ug-activity-item"><span class="ug-activity-icon blue"><i class="fas fa-layer-group"></i></span><span><strong>Course registration</strong><small>{{ $registeredCount ? $registeredCount . ' courses across ' . max(1, $registrationSemesters->count()) . ' semester(s)' : 'No courses registered yet' }}</small></span><i class="fas fa-chevron-right ug-chevron"></i></a>
                    <a href="{{ url('/student-result') }}" class="ug-activity-item"><span class="ug-activity-icon purple"><i class="fas fa-file-signature"></i></span><span><strong>Approved results</strong><small>{{ $resultCourses ? $resultCourses . ' approved course result(s)' : 'Results are not published yet' }}{{ $lastResultUpdate ? ' · Updated ' . date('d M Y', strtotime($lastResultUpdate)) : '' }}</small></span><i class="fas fa-chevron-right ug-chevron"></i></a>
                    <a href="{{ url('/payment') }}" class="ug-activity-item"><span class="ug-activity-icon green"><i class="fas fa-receipt"></i></span><span><strong>Payments</strong><small>{{ $pendingPayments ? $formatMoney($pendingAmount) . ' pending' : 'No pending payments' }}</small></span><i class="fas fa-chevron-right ug-chevron"></i></a>
                </div>
            </section>
        <section class="ug-panel ug-results-panel">
            <div class="ug-panel-heading">
                <div><span class="ug-kicker">{{ $currentSession ?: 'Current session' }}</span><h2>Approved results</h2></div>
                <span class="ug-approved-badge"><i class="fas fa-check-circle"></i> Approved</span>
            </div>
            @if ($sessionResults->isNotEmpty())
                <div class="ug-result-list">
                    @foreach ($sessionResults->take(6) as $result)
                        <div class="ug-result-row">
                            <span class="ug-result-code">{{ $result->code }}</span>
                            <span class="ug-result-context">{{ $result->semester ?: 'Semester' }}{{ $result->level ? ' · ' . $result->level . ' Level' : '' }}</span>
                            <span class="ug-result-total">{{ $result->total !== null ? $result->total : '—' }}</span>
                            <strong class="ug-result-grade">{{ $result->grade ?: '—' }}</strong>
                        </div>
                    @endforeach
                </div>
                @if ($sessionResults->count() > 6)
                    <a href="{{ url('/student-result') }}" class="ug-results-more">View all {{ $resultCourses }} approved results <i class="fas fa-arrow-right"></i></a>
                @endif
            @else
                <div class="ug-results-empty"><i class="fas fa-question-circle"></i><div><strong>No approved results yet</strong><small>Published results for {{ $currentSession ?: 'this session' }} will appear here.</small></div></div>
            @endif
        </section>
        </div>

        <section class="ug-quick-links"><span class="ug-kicker">Quick access</span><div class="ug-quick-grid"><a href="{{ url('/student course registration') }}"><i class="fas fa-book"></i><span>Courses</span></a><a href="{{ url('/student-result') }}"><i class="fas fa-chart-bar"></i><span>Results</span></a><a href="{{ url('/payment') }}"><i class="fas fa-credit-card"></i><span>Payments</span></a><a href="{{ url('/profile') }}"><i class="fas fa-user-edit"></i><span>Profile</span></a></div></section>
    </div>
</div>

<style>
    .ug-dashboard-page{background:#f5f7fb;min-height:calc(100vh - 70px);padding-bottom:2rem}.ug-dashboard-page .page-wrapper{max-width:1440px;margin:auto;padding:18px}.ug-welcome-card{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:24px 28px;border-radius:22px;background:linear-gradient(120deg,#0e4fcb,#143a92 65%,#263f9f);color:#fff;box-shadow:0 12px 28px rgba(16,68,161,.18);overflow:hidden;position:relative}.ug-welcome-card:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;right:-85px;top:-145px;background:rgba(255,255,255,.1)}.ug-welcome-copy{position:relative;z-index:1}.ug-eyebrow,.ug-kicker{font-size:.72rem;text-transform:uppercase;letter-spacing:.09em;font-weight:700;opacity:.8}.ug-eyebrow i{margin-right:5px}.ug-welcome-card h1{font-size:clamp(1.45rem,3vw,2.15rem);margin:.45rem 0 .25rem;font-weight:700}.ug-welcome-card p{margin:0;color:rgba(255,255,255,.8)}.ug-meta-row{display:flex;flex-wrap:wrap;gap:.55rem 1rem;margin-top:1rem;font-size:.8rem;color:#dbe7ff}.ug-meta-row i{margin-right:.35rem}.ug-avatar-wrap{position:relative;z-index:1}.ug-avatar{width:76px;height:76px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.65);background:#e4edff}.ug-avatar-initials{display:grid;place-items:center;color:#0e4fcb;font-size:1.35rem;font-weight:800}.ug-online-dot{position:absolute;right:3px;bottom:5px;width:14px;height:14px;background:#35d17a;border:3px solid #fff;border-radius:50%}.ug-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:18px 0}.ug-stat-card{padding:17px 18px;border-radius:17px;background:#fff;box-shadow:0 5px 18px rgba(21,43,77,.06);border:1px solid #e8edf5;display:flex;flex-direction:column;gap:3px;text-decoration:none;color:#18243a;min-width:0}.ug-stat-card:hover{transform:translateY(-2px);box-shadow:0 9px 24px rgba(21,43,77,.1);color:#18243a}.ug-stat-icon{width:35px;height:35px;border-radius:10px;display:grid;place-items:center;margin-bottom:5px;color:#fff}.ug-stat-label{font-size:.77rem;color:#63708a}.ug-stat-card strong{font-size:1.35rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-stat-card small{font-size:.72rem;color:#7c8799}.ug-stat-blue .ug-stat-icon{background:#2176f3}.ug-stat-purple .ug-stat-icon{background:#7855dc}.ug-stat-green .ug-stat-icon{background:#20a66a}.ug-stat-orange .ug-stat-icon{background:#ee9343}.ug-content-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(300px,.85fr);gap:18px;margin-bottom:18px}.ug-panel{background:#fff;border:1px solid #e8edf5;border-radius:18px;box-shadow:0 5px 18px rgba(21,43,77,.05);padding:21px}.ug-panel-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:17px}.ug-panel h2{font-size:1.2rem;margin:3px 0 0;color:#17233a}.ug-text-link{font-size:.77rem;font-weight:700;color:#0e5bd6;text-decoration:none;white-space:nowrap}.ug-text-link i{margin-left:4px;font-size:.65rem}.ug-profile-main{display:flex;align-items:center;gap:13px;margin-bottom:18px}.ug-profile-photo{width:62px;height:62px;border-radius:14px;overflow:hidden;background:#edf2fa;flex:0 0 auto}.ug-profile-photo img{width:100%;height:100%;object-fit:cover}.ug-profile-name h3{font-size:1.04rem;margin:0 0 3px;color:#1d2b44}.ug-profile-name p{font-size:.78rem;margin:0;color:#72809a}.ug-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 18px}.ug-detail-grid div{min-width:0}.ug-detail-grid span{display:block;font-size:.7rem;color:#8090a8;margin-bottom:3px}.ug-detail-grid strong{display:block;font-size:.82rem;color:#26344c;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ug-status-line{border-top:1px solid #eef1f6;margin-top:18px;padding-top:13px;display:flex;align-items:center;gap:7px;font-size:.75rem;color:#73819a}.ug-status-line strong{margin-left:auto;color:#1c9a61}.ug-status-dot{width:8px;height:8px;border-radius:50%;background:#26b873}.ug-activity-list{display:flex;flex-direction:column}.ug-activity-item{display:flex;align-items:center;gap:11px;padding:13px 0;border-bottom:1px solid #eef1f6;text-decoration:none;color:#22314b}.ug-activity-item:last-child{border-bottom:0;padding-bottom:0}.ug-activity-item:first-child{padding-top:0}.ug-activity-item strong,.ug-activity-item small{display:block}.ug-activity-item strong{font-size:.83rem}.ug-activity-item small{font-size:.71rem;color:#8190a7;margin-top:3px}.ug-activity-icon{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;flex:0 0 auto}.ug-activity-icon.blue{background:#e7f0ff;color:#2169d5}.ug-activity-icon.purple{background:#f0eaff;color:#7751d0}.ug-activity-icon.green{background:#e3f8ef;color:#168957}.ug-chevron{margin-left:auto;color:#9aa6b8;font-size:.65rem}.ug-lectures-panel{margin-bottom:18px}.ug-lecture-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.ug-day-column h3{font-size:.88rem;margin:0 0 11px;color:#263650}.ug-day-column h3 i{color:#1d6ae1;margin-right:5px}.ug-lecture-card{display:flex;gap:12px;padding:13px;border:1px solid #e8edf5;border-left:3px solid #2674df;border-radius:12px;margin-bottom:9px;background:#fbfcff}.ug-lecture-time{font-size:.68rem;font-weight:700;color:#2869c8;min-width:83px;padding-top:2px}.ug-lecture-info{display:flex;flex-direction:column;gap:3px;min-width:0}.ug-lecture-info strong{font-size:.82rem;color:#25344e}.ug-lecture-info span,.ug-lecture-info small{font-size:.7rem;color:#7a889e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ug-lecture-info i{width:13px;color:#9aa8bc}.ug-empty-state{border:1px dashed #dbe3ef;border-radius:12px;padding:21px 12px;text-align:center;color:#94a1b4}.ug-empty-state i{font-size:1.35rem;color:#b1bfd1}.ug-empty-state p{font-size:.8rem;margin:7px 0 2px;color:#53647e}.ug-empty-state small{font-size:.7rem}.ug-quick-links{padding:0 3px}.ug-quick-links>.ug-kicker{display:block;color:#79879d;margin-bottom:10px}.ug-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.ug-quick-grid a{display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 10px;border-radius:12px;background:#fff;border:1px solid #e8edf5;color:#31415d;text-decoration:none;font-size:.78rem;font-weight:700;box-shadow:0 4px 12px rgba(21,43,77,.04)}.ug-quick-grid a i{color:#1f6cdb}.ug-quick-grid a:hover{border-color:#b9d2fb;background:#f8fbff}@media(max-width:900px){.ug-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ug-content-grid{grid-template-columns:1fr}}@media(max-width:600px){.ug-dashboard-page .page-wrapper{padding:11px}.ug-welcome-card{padding:19px 17px;border-radius:17px}.ug-avatar{width:59px;height:59px}.ug-avatar-wrap{align-self:flex-start}.ug-welcome-card h1{font-size:1.35rem}.ug-welcome-card p{font-size:.75rem;max-width:245px}.ug-meta-row{font-size:.68rem;gap:.35rem .7rem}.ug-stat-grid{gap:9px;margin:12px 0}.ug-stat-card{padding:13px 12px;border-radius:13px}.ug-stat-card strong{font-size:1.05rem}.ug-stat-card small{font-size:.64rem}.ug-stat-label{font-size:.68rem}.ug-stat-icon{width:30px;height:30px;font-size:.78rem;border-radius:8px}.ug-panel{padding:16px 14px;border-radius:15px}.ug-panel-heading{margin-bottom:14px}.ug-panel h2{font-size:1.02rem}.ug-kicker{font-size:.63rem}.ug-profile-main{margin-bottom:15px}.ug-detail-grid{gap:11px 12px}.ug-detail-grid strong{font-size:.74rem}.ug-detail-grid span{font-size:.63rem}.ug-status-line{font-size:.68rem}.ug-lecture-columns{grid-template-columns:1fr;gap:17px}.ug-lecture-card{padding:11px 10px;gap:9px}.ug-lecture-time{min-width:72px;font-size:.62rem}.ug-lecture-info strong{font-size:.75rem}.ug-lecture-info span,.ug-lecture-info small{font-size:.64rem}.ug-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.ug-quick-grid a{font-size:.71rem;padding:11px 7px}.ug-text-link{font-size:.67rem}}
</style>
<style>
    /* Use the portal primary colour consistently across the dashboard. */
    .ug-welcome-card{background:linear-gradient(120deg,#3EA1E4,#2f8fcf 68%,#267bb8);box-shadow:0 12px 28px rgba(62,161,228,.22)}
    .ug-welcome-card{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(250px,.8fr);align-items:center}
    .ug-welcome-academic{display:flex;align-items:center;gap:18px;position:relative;z-index:1}
    .ug-welcome-academic>div{min-width:0}
    .ug-welcome-academic span{display:block;font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.72);margin-bottom:3px}
    .ug-welcome-academic strong{display:block;color:#fff;font-size:.82rem;max-width:175px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .ug-welcome-profile{display:inline-flex;align-items:center;gap:6px;padding:9px 11px;border:1px solid rgba(255,255,255,.48);border-radius:9px;color:#fff;font-size:.72rem;font-weight:700;text-decoration:none;white-space:nowrap}
    .ug-welcome-profile:hover{background:rgba(255,255,255,.15);color:#fff}
    .ug-content-grid{grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);align-items:stretch}
    .ug-activity-panel{order:2}.ug-results-panel{order:1}
    .ug-welcome-academic{grid-column:2;grid-row:1}.ug-avatar-wrap{grid-column:3;grid-row:1;justify-self:end}
    .ug-avatar-initials{color:#3EA1E4}
    .ug-stat-blue .ug-stat-icon{background:#3EA1E4}
    .ug-text-link,.ug-quick-grid a i{color:#258ac8}
    .ug-activity-icon.blue{background:#e4f3fc;color:#258ac8}
    .ug-activity-icon.purple{background:#edf4ff;color:#3978b4}
    .ug-lecture-card{border-left-color:#3EA1E4}
    .ug-results-panel{margin-bottom:18px}
    .ug-approved-badge{font-size:.68rem;font-weight:700;color:#168957;background:#e3f8ef;border-radius:999px;padding:6px 9px;white-space:nowrap}
    .ug-result-list{display:flex;flex-direction:column}
    .ug-result-row{display:grid;grid-template-columns:1.2fr 1.3fr .5fr .45fr;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #eef1f6;font-size:.76rem}
    .ug-result-row:last-child{border-bottom:0}
    .ug-result-code{font-weight:800;color:#263b59}
    .ug-result-context{color:#7b8aa0;font-size:.7rem}
    .ug-result-total{color:#5d6e86;text-align:center}
    .ug-result-grade{justify-self:end;color:#258ac8;background:#eaf5fd;border-radius:8px;padding:5px 9px;min-width:34px;text-align:center}
    .ug-results-more{display:inline-block;margin-top:11px;font-size:.72rem;font-weight:700;color:#258ac8;text-decoration:none}
    .ug-results-more i{font-size:.62rem;margin-left:4px}
    .ug-results-empty{display:flex;align-items:center;gap:12px;padding:8px 0;color:#8b99ac}
    .ug-results-empty>i{font-size:1.5rem;color:#a9b9ca}
    .ug-results-empty strong,.ug-results-empty small{display:block}
    .ug-results-empty strong{font-size:.8rem;color:#53647e}
    .ug-results-empty small{font-size:.7rem;margin-top:3px}
    @media(max-width:900px){.ug-content-grid{grid-template-columns:1fr}}
    @media(max-width:600px){.ug-welcome-card{grid-template-columns:minmax(0,1fr) auto;align-items:start}.ug-welcome-academic{grid-column:1/-1;grid-row:2;display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.35fr) auto;gap:9px;margin-top:15px;padding-top:13px;border-top:1px solid rgba(255,255,255,.2)}.ug-avatar-wrap{grid-column:2;grid-row:1}.ug-welcome-academic strong{font-size:.72rem;max-width:125px}.ug-welcome-profile{padding:8px 9px;font-size:.64rem;align-self:end}.ug-result-row{grid-template-columns:1fr auto;gap:4px 10px;padding:11px 0}.ug-result-context{grid-column:1;grid-row:2}.ug-result-total{grid-column:2;grid-row:2;text-align:right;color:#7b8aa0;font-size:.7rem}.ug-result-grade{grid-column:2;grid-row:1}.ug-approved-badge{font-size:.6rem;padding:5px 7px}}
</style>
<style>
    @media(max-width:600px){
        .ug-welcome-card{grid-template-columns:minmax(0,1fr) 58px;column-gap:12px;row-gap:0;padding:18px 16px}
        .ug-welcome-copy{min-width:0}
        .ug-welcome-card h1{font-size:1.28rem;line-height:1.18;overflow-wrap:anywhere}
        .ug-welcome-card p{line-height:1.35;max-width:none}
        .ug-meta-row{display:flex;flex-direction:column;align-items:flex-start;gap:4px;margin-top:11px}
        .ug-avatar-wrap{grid-column:2;grid-row:1;align-self:start}
        .ug-avatar{width:56px;height:56px}
        .ug-welcome-academic{grid-column:1/-1;grid-row:2;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:11px 14px;margin-top:15px;padding-top:13px}
        .ug-welcome-academic>div{min-width:0}
        .ug-welcome-academic>div:nth-child(2){grid-column:2}
        .ug-welcome-academic span{font-size:.6rem}
        .ug-welcome-academic strong{max-width:none;font-size:.72rem;line-height:1.25;white-space:normal;overflow:visible;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
        .ug-welcome-profile{grid-column:1/-1;grid-row:3;justify-content:center;width:100%;padding:8px 10px;margin-top:1px}
    }
</style>

{{-- Keep the existing safety prompt for students whose current level has not been confirmed. --}}
@if (session('level_flag') == 0)
    <div class="modal fade" id="levelUpdateModal" tabindex="-1" aria-labelledby="levelUpdateModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-warning text-dark"><h5 class="modal-title" id="levelUpdateModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Update Your Current Level</h5></div><div class="modal-body"><div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Your current level needs to be updated. Please select your correct academic level below.</div><form id="levelUpdateForm" action="{{ route('update.student.level') }}" method="POST">@csrf<div class="mb-3"><label for="currentLevel" class="form-label fw-bold">Select Your Current Level:</label><select class="form-select" id="currentLevel" name="level" required><option value="">Choose your level...</option>@foreach ([100,200,300,400,500,600,700] as $option)<option value="{{ $option }}">{{ $option }} Level</option>@endforeach</select></div><small class="text-muted">You can change this later from your profile.</small></form></div><div class="modal-footer"><button type="button" class="btn btn-success" onclick="updateLevel()"><i class="fas fa-save me-2"></i>Update Level</button></div></div></div>
    </div>
    <script>document.addEventListener('DOMContentLoaded',function(){new bootstrap.Modal(document.getElementById('levelUpdateModal')).show()});function updateLevel(){const f=document.getElementById('levelUpdateForm'),s=document.getElementById('currentLevel');if(!s.value){if(window.swal)swal('Oops!!!','Please select your current level before proceeding.','warning');return}if(confirm('Are you sure '+s.value+' is your correct current level?'))f.submit()}</script>
@endif
