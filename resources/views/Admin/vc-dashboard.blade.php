@php
    use App\Models\Staff;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;

    $staff = Staff::where('username', session('username'))->first();
    $name = trim((string) ($staff->name ?? session('username') ?? 'Vice-Chancellor'));
    $staffId = trim((string) ($staff->username ?? session('username') ?? ''));
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $photo = trim((string) ($staff->picture ?? ''));
    $photoUrl = $photo ? asset('storage/picture/' . $photo) : null;
    $initials = collect(preg_split('/\s+/', $name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');

    // Count distinct result batches rather than loading every result row into the dashboard.
    $resultsAwaitingCount = (int) DB::table('results')
        ->where('approve', 'cs')
        ->selectRaw("COUNT(DISTINCT CONCAT_WS('|', code, session, semester)) AS batch_count")
        ->value('batch_count');
    $changeOfCourseAwaiting = DB::table('change_of_course')->where('status', 'Awaiting VC')->count();
    $interUniversityAwaiting = DB::table('inter_university_transfer')->where('status', 'Awaiting VC')->count();

    $pgOverview = Cache::remember('vc-dashboard.pg-admission-overview', 60, function () {
        try {
            $baseUrl = rtrim((string) config('app.pg_admission_api_url', env('PG_ADMISSION_API_URL', 'https://pg.umstad.online/api/v1/pg-admissions')), '/');
            $apiKey = config('app.pg_admission_api_key', env('PG_ADMISSION_API_KEY'));
            $response = Http::withHeaders(['X-API-Key' => $apiKey, 'Accept' => 'application/json'])
                ->withoutVerifying()
                ->timeout(6)
                ->get($baseUrl . '/overview', ['api_key' => $apiKey]);
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Throwable $exception) {
            return [];
        }
    });
    // Cleared applications are the PG applications ready for the VC decision.
    $pgAwaiting = (int) ($pgOverview['cleared'] ?? 0);
    $totalAwaiting = $resultsAwaitingCount + $pgAwaiting + $changeOfCourseAwaiting + $interUniversityAwaiting;
@endphp

<div class="main-body vc-dashboard-page">
    <div class="page-wrapper">
        <section class="vc-welcome-card">
            <div class="vc-welcome-copy">
                <span class="vc-eyebrow"><i class="fas fa-landmark"></i> Executive dashboard</span>
                <h1>Welcome back, {{ \Illuminate\Support\Str::before($name, ' ') ?: $name }}.</h1>
                <p>Stay informed about the university matters that require your attention.</p>
                <div class="vc-meta-row">
                    @if ($staffId)<span><i class="fas fa-id-badge"></i> {{ $staffId }}</span>@endif
                    <span><i class="fas fa-calendar-alt"></i> {{ $currentSession ?: 'Current session' }}</span>
                </div>
            </div>
            <div class="vc-avatar-wrap">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}" class="vc-avatar">
                @else
                    <div class="vc-avatar vc-avatar-initials">{{ $initials ?: 'VC' }}</div>
                @endif
                <span class="vc-online-dot" title="Active account"></span>
            </div>
            <div class="vc-welcome-details">
                <div><span>Office</span><strong>Vice-Chancellor</strong></div>
                <div><span>Decisions awaiting</span><strong>{{ number_format($totalAwaiting) }}</strong></div>
                <a href="{{ url('/profile') }}" class="vc-profile-link"><i class="fas fa-user-edit"></i> View profile</a>
            </div>
        </section>

        <section class="vc-summary-heading">
            <div><span class="vc-kicker">Executive overview</span><h2>Awaiting your decision</h2></div>
            <span class="vc-session-pill"><i class="fas fa-globe"></i> All academic sessions</span>
        </section>

        <section class="vc-summary-grid" aria-label="Items awaiting Vice-Chancellor decision">
            <a href="{{ url('/approve results') }}" class="vc-summary-card vc-tone-blue">
                <span class="vc-summary-icon"><i class="fas fa-square-check"></i></span>
                <span class="vc-summary-label">Results awaiting VC approval</span>
                <strong>{{ number_format($resultsAwaitingCount) }}</strong>
                <small>Across all academic sessions</small>
                <i class="fas fa-arrow-up-right-from-square vc-card-arrow"></i>
            </a>
            <a href="{{ url('/pg-admission') }}" class="vc-summary-card vc-tone-purple">
                <span class="vc-summary-icon"><i class="fas fa-user-graduate"></i></span>
                <span class="vc-summary-label">Postgraduate admissions</span>
                <strong>{{ number_format($pgAwaiting) }}</strong>
                <small>Awaiting VC approval</small>
                <i class="fas fa-arrow-up-right-from-square vc-card-arrow"></i>
            </a>
            <a href="{{ url('/change-of-course/admin') }}" class="vc-summary-card vc-tone-orange">
                <span class="vc-summary-icon"><i class="fas fa-shuffle"></i></span>
                <span class="vc-summary-label">Change of course</span>
                <strong>{{ number_format($changeOfCourseAwaiting) }}</strong>
                <small>Applications awaiting approval</small>
                <i class="fas fa-arrow-up-right-from-square vc-card-arrow"></i>
            </a>
            <a href="{{ url('/inter-university-transfer/admin') }}" class="vc-summary-card vc-tone-green">
                <span class="vc-summary-icon"><i class="fas fa-right-left"></i></span>
                <span class="vc-summary-label">Inter-university transfers</span>
                <strong>{{ number_format($interUniversityAwaiting) }}</strong>
                <small>Applications awaiting approval</small>
                <i class="fas fa-arrow-up-right-from-square vc-card-arrow"></i>
            </a>
        </section>

        <section class="vc-panel vc-journey-panel">
            <div class="vc-panel-heading"><div><span class="vc-kicker">University workspace</span><h2>Quick access</h2><p>Open the university areas you may need for oversight and review.</p></div><i class="fas fa-compass vc-panel-icon"></i></div>
            <div class="vc-journey-grid">
                <a href="{{ url('/students list') }}" class="vc-journey-item"><span class="vc-journey-number">01</span><span><strong>Student records</strong><small>View the university’s current student records.</small></span><i class="fas fa-chevron-right"></i></a>
                <a href="{{ url('/staff') }}" class="vc-journey-item"><span class="vc-journey-number">02</span><span><strong>Staff records</strong><small>View staff appointments and university units.</small></span><i class="fas fa-chevron-right"></i></a>
                <a href="{{ url('/recruitment') }}" class="vc-journey-item"><span class="vc-journey-number">03</span><span><strong>Recruitment</strong><small>Review the university’s recruitment activities.</small></span><i class="fas fa-chevron-right"></i></a>
                <a href="{{ url('/fees due') }}" class="vc-journey-item"><span class="vc-journey-number">04</span><span><strong>Fees and payments</strong><small>View the university’s payment position.</small></span><i class="fas fa-chevron-right"></i></a>
                <a href="{{ url('/program') }}" class="vc-journey-item"><span class="vc-journey-number">05</span><span><strong>Academic structure</strong><small>View faculties, departments and programmes.</small></span><i class="fas fa-chevron-right"></i></a>
                <a href="{{ url('/election live') }}" class="vc-journey-item"><span class="vc-journey-number">06</span><span><strong>Live election</strong><small>Monitor the current election activity.</small></span><i class="fas fa-chevron-right"></i></a>
            </div>
        </section>
    </div>
</div>

<style>
    .vc-dashboard-page{background:#f5f7fb;min-height:calc(100vh - 70px);padding-bottom:2rem}.vc-dashboard-page .page-wrapper{max-width:1440px;margin:auto;padding:18px}.vc-welcome-card{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(250px,.78fr);align-items:center;gap:20px;padding:24px 28px;border-radius:22px;background:linear-gradient(120deg,#3ea1e4,#2f8fcf 68%,#267bb8);color:#fff;box-shadow:0 12px 28px rgba(62,161,228,.22);overflow:hidden;position:relative}.vc-welcome-card:after{content:"";position:absolute;width:290px;height:290px;border-radius:50%;right:-100px;top:-160px;background:rgba(255,255,255,.11)}.vc-welcome-copy,.vc-welcome-details,.vc-avatar-wrap{position:relative;z-index:1}.vc-eyebrow,.vc-kicker{font-size:.72rem;text-transform:uppercase;letter-spacing:.09em;font-weight:700;opacity:.82}.vc-eyebrow i{margin-right:5px}.vc-welcome-card h1{font-size:clamp(1.45rem,3vw,2.15rem);margin:.45rem 0 .25rem;font-weight:700}.vc-welcome-card p{margin:0;color:rgba(255,255,255,.82)}.vc-meta-row{display:flex;flex-wrap:wrap;gap:.55rem 1rem;margin-top:1rem;font-size:.8rem;color:#dbeeff}.vc-meta-row i{margin-right:.35rem}.vc-avatar-wrap{justify-self:end}.vc-avatar{width:78px;height:78px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.72);background:#e4f3fc;box-shadow:0 8px 18px rgba(23,78,122,.2)}.vc-avatar-initials{display:grid;place-items:center;color:#2783bc;font-size:1.35rem;font-weight:800}.vc-online-dot{position:absolute;right:2px;bottom:5px;width:14px;height:14px;background:#35d17a;border:3px solid #fff;border-radius:50%}.vc-welcome-details{display:grid;grid-template-columns:1fr 1fr;gap:14px 18px;min-width:0}.vc-welcome-details>div{min-width:0}.vc-welcome-details span{display:block;font-size:.63rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.72);margin-bottom:3px}.vc-welcome-details strong{display:block;color:#fff;font-size:.82rem;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.vc-profile-link{grid-column:1/-1;display:inline-flex;justify-content:center;align-items:center;gap:6px;padding:9px 11px;border:1px solid rgba(255,255,255,.48);border-radius:9px;color:#fff;font-size:.72rem;font-weight:700;text-decoration:none}.vc-profile-link:hover{background:rgba(255,255,255,.15);color:#fff}.vc-summary-heading{display:flex;justify-content:space-between;align-items:end;gap:15px;margin:22px 2px 12px}.vc-summary-heading h2{margin:3px 0 0;color:#17233a;font-size:1.16rem}.vc-summary-heading .vc-kicker{color:#79879d;opacity:1}.vc-session-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#e4f3fc;color:#258ac8;font-size:.69rem;font-weight:800;white-space:nowrap}.vc-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.vc-summary-card{position:relative;display:flex;flex-direction:column;gap:3px;min-width:0;padding:17px 18px;border:1px solid #e8edf5;border-left:4px solid transparent;border-radius:17px;background:#fff;box-shadow:0 5px 18px rgba(21,43,77,.06);text-decoration:none;color:#18243a;transition:transform .2s ease,box-shadow .2s ease}.vc-summary-card:hover{transform:translateY(-2px);box-shadow:0 9px 24px rgba(21,43,77,.1);color:#18243a}.vc-summary-icon{width:35px;height:35px;display:grid;place-items:center;margin-bottom:5px;border-radius:10px;color:#fff}.vc-summary-label{font-size:.77rem;color:#63708a}.vc-summary-card strong{font-size:1.35rem;line-height:1.2}.vc-summary-card small{font-size:.72rem;color:#7c8799;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.vc-card-arrow{position:absolute;top:16px;right:15px;color:#9aa6b8;font-size:.68rem}.vc-tone-blue{border-left-color:#3ea1e4}.vc-tone-blue .vc-summary-icon{background:#3ea1e4}.vc-tone-purple{border-left-color:#7855dc}.vc-tone-purple .vc-summary-icon{background:#7855dc}.vc-tone-orange{border-left-color:#ee9343}.vc-tone-orange .vc-summary-icon{background:#ee9343}.vc-tone-green{border-left-color:#20a66a}.vc-tone-green .vc-summary-icon{background:#20a66a}.vc-panel{margin-top:18px;padding:21px;border:1px solid #e8edf5;border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(21,43,77,.05)}.vc-panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:17px}.vc-panel-heading h2{margin:3px 0 3px;color:#17233a;font-size:1.08rem}.vc-panel-heading p{margin:0;color:#8493a7;font-size:.72rem}.vc-panel-icon{color:#a2b1c2;font-size:1.05rem;margin-top:3px}.vc-panel-heading .vc-kicker{color:#79879d;opacity:1}.vc-journey-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.vc-journey-item{display:flex;align-items:center;gap:11px;min-width:0;padding:13px;border:1px solid #e8edf5;border-radius:12px;background:#fbfcff;color:#22314b;text-decoration:none}.vc-journey-item:hover{border-color:#b9d2fb;background:#f4f9ff;color:#1d5fa5}.vc-journey-number{display:grid;place-items:center;width:31px;height:31px;flex:0 0 auto;border-radius:9px;background:#e4f3fc;color:#258ac8;font-size:.68rem;font-weight:800}.vc-journey-item span:nth-child(2){display:flex;flex-direction:column;gap:3px;min-width:0}.vc-journey-item strong{font-size:.78rem}.vc-journey-item small{overflow:hidden;color:#7b8aa0;font-size:.68rem;text-overflow:ellipsis;white-space:nowrap}.vc-journey-item>i{margin-left:auto;color:#9aa6b8;font-size:.65rem}@media(max-width:1050px){.vc-welcome-card{grid-template-columns:minmax(0,1fr) auto}.vc-welcome-details{grid-column:1/-1;grid-row:2;grid-template-columns:repeat(3,minmax(0,1fr));padding-top:14px;border-top:1px solid rgba(255,255,255,.2)}.vc-profile-link{grid-column:auto}.vc-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.vc-dashboard-page .page-wrapper{padding:11px}.vc-welcome-card{grid-template-columns:minmax(0,1fr) 58px;column-gap:12px;row-gap:0;padding:18px 16px;border-radius:17px}.vc-welcome-card h1{font-size:1.28rem;line-height:1.18;overflow-wrap:anywhere}.vc-welcome-card p{font-size:.75rem;line-height:1.35}.vc-meta-row{display:flex;flex-direction:column;align-items:flex-start;gap:4px;margin-top:11px;font-size:.68rem}.vc-avatar-wrap{grid-column:2;grid-row:1;align-self:start}.vc-avatar{width:56px;height:56px}.vc-welcome-details{grid-column:1/-1;grid-row:2;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:11px 14px;margin-top:15px;padding-top:13px}.vc-welcome-details>div:nth-child(2){grid-column:2}.vc-welcome-details span{font-size:.6rem}.vc-welcome-details strong{font-size:.72rem;line-height:1.25;white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.vc-profile-link{grid-column:1/-1;width:100%;margin-top:1px;padding:8px 10px;font-size:.64rem}.vc-summary-heading{align-items:flex-start;margin:16px 2px 10px}.vc-summary-heading h2{font-size:1.02rem}.vc-kicker{font-size:.63rem}.vc-session-pill{padding:5px 8px;font-size:.62rem}.vc-summary-grid{gap:9px}.vc-summary-card{padding:13px 12px;border-radius:13px;border-left-width:3px}.vc-summary-icon{width:30px;height:30px;border-radius:8px;font-size:.78rem}.vc-summary-label{font-size:.68rem}.vc-summary-card strong{font-size:1.05rem}.vc-summary-card small{font-size:.64rem}.vc-card-arrow{top:13px;right:11px;font-size:.6rem}.vc-panel{padding:16px 14px;border-radius:15px}.vc-panel-heading{margin-bottom:14px}.vc-panel-heading h2{font-size:1.02rem}.vc-panel-heading p{font-size:.67rem}.vc-journey-grid{grid-template-columns:1fr;gap:8px}.vc-journey-item{padding:11px 10px}.vc-journey-item strong{font-size:.73rem}.vc-journey-item small{font-size:.63rem}}
</style>
