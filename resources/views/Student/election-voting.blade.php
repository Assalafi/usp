@php
    $category = $category ?? 'General';
    $positions = collect($positions ?? $poss ?? [])->where('category', $category)->values();
    $visibleCandidates = collect($visibleCandidates ?? $data ?? [])->where('category', $category)->values();
    $status = strtoupper((string) ($status ?? 'CLOSE'));
    $hasVoted = (bool) ($vote ?? false);
    $eligibilityMessage = $eligibilityMessage ?? 'Your account is not currently eligible for this election.';
    $programDuration = !empty($student->program)
        ? (int) DB::table('program')->where('code', $student->program)->value('duration')
        : 0;
    if ($programDuration <= 0) {
        $programDuration = (int) ($student->duration ?? 0);
    }
    $currentLevelNumber = (int) preg_replace('/[^0-9]/', '', (string) ($student->level ?? ''));
    $isFinalYearStudent = $programDuration > 0
        && $currentLevelNumber > 0
        && $currentLevelNumber >= ($programDuration * 100);
    if ($isFinalYearStudent) {
        $eligibility = false;
        $eligibilityMessage = 'Final-year students are not eligible to vote in this election. Published results will remain available after voting closes.';
    }
    $open = $status === 'OPEN';
    $canVote = $open && !$hasVoted && ($eligibility ?? true);
    $scopeIcon = $category === 'General' ? 'fa-users' : 'fa-map-marker-alt';
    $electionPhase = 'upcoming';
    if (!$open) {
        $startValue = DB::table('election_settings')->where('description', 'Election Start')->value('value');
        $endValue = DB::table('election_settings')->where('description', 'Election End')->value('value');
        $startAt = null;
        $endAt = null;
        try {
            $startAt = $startValue ? \Carbon\Carbon::parse($startValue) : null;
            $endAt = $endValue ? \Carbon\Carbon::parse($endValue) : null;
        } catch (\Throwable $exception) {
            $startAt = null;
            $endAt = null;
        }
        $hasCategoryVotes = DB::table('election_votes')->where('category', $category)->exists();
        if (($endAt && now()->gte($endAt)) || (!$startAt && !$endAt && $hasCategoryVotes) || ($startAt && now()->gte($startAt) && $hasCategoryVotes)) {
            $electionPhase = 'ended';
        }
    }
    $statusLabel = $open ? 'Open for voting' : ($electionPhase === 'ended' ? 'Voting closed' : 'Not yet open');
    $submittedChoices = collect();
    if ($hasVoted) {
        $submittedChoices = DB::table('election_votes as votes')
            ->leftJoin('election_candidates as candidates', function ($join) {
                $join->on('candidates.candidate', '=', 'votes.candidate')
                    ->on('candidates.category', '=', 'votes.category')
                    ->on('candidates.position', '=', 'votes.position');
            })
            ->where('votes.username', session('id_number', session('username')))
            ->where('votes.category', $category)
            ->whereIn('votes.position', $positions->pluck('position')->all())
            ->orderBy('votes.position')
            ->get(['votes.position', 'votes.candidate', 'candidates.name']);
    }
    $categoryDescriptions = [
        'General' => 'Choose one candidate for each general office.',
        'Faculty Rep' => 'Choose a representative for your current level and faculty.',
        'Hostel Rep' => 'Choose a representative for your allocated hostel.',
        'Lga Rep' => 'Choose a representative for your local government area.',
    ];
@endphp

<style>
    .ug-election-shell{--ue-primary:#1769aa;--ue-primary-2:#3ea1e4;--ue-ink:#15324b;--ue-muted:#6b7f90;--ue-border:#dfeaf3;--ue-soft:#f4f9fd;max-width:1180px;margin:0 auto;padding:18px 12px 42px;color:var(--ue-ink)}
    .ug-election-hero{position:relative;overflow:hidden;border-radius:24px;padding:28px 28px 24px;background:linear-gradient(135deg,#0c5794 0%,#2088c8 52%,#3ea1e4 100%);color:#fff;box-shadow:0 18px 42px rgba(20,93,141,.18)}
    .ug-election-hero:after{content:"";position:absolute;width:230px;height:230px;border-radius:50%;right:-82px;top:-110px;background:rgba(255,255,255,.12)}
    .ug-election-kicker{position:relative;z-index:1;margin:0 0 7px;font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;font-weight:700;opacity:.8}
    .ug-election-hero h1{position:relative;z-index:1;margin:0;font-size:clamp(1.45rem,3vw,2.15rem);font-weight:800;color:#fff}
    .ug-election-hero p{position:relative;z-index:1;max-width:680px;margin:8px 0 0;color:rgba(255,255,255,.88);font-size:.98rem}
    .ug-election-meta{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
    .ug-election-pill{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:7px 12px;background:rgba(255,255,255,.12);font-size:.78rem;font-weight:700;color:#fff}
    .ug-election-pill.is-open{background:#e9fff4;color:#116a43;border-color:#c8f3dd}.ug-election-pill.is-closed{background:#fff4e6;color:#8a4b05;border-color:#ffe1ba}
    .ug-election-notice{display:flex;gap:13px;align-items:flex-start;margin:18px 0;padding:16px 18px;border:1px solid var(--ue-border);border-radius:16px;background:#fff;box-shadow:0 8px 22px rgba(33,75,108,.06)}
    .ug-election-notice i{font-size:1.15rem;margin-top:2px;color:var(--ue-primary-2)}.ug-election-notice strong{display:block;margin-bottom:3px;font-size:.98rem}.ug-election-notice p{margin:0;color:var(--ue-muted);font-size:.88rem;line-height:1.55}
    .ug-election-completion{margin:18px 0;border:1px solid #cfe9dc;border-radius:20px;background:linear-gradient(145deg,#f8fffb 0%,#fff 68%);box-shadow:0 12px 30px rgba(24,103,72,.08);overflow:hidden}.ug-election-completion-main{display:flex;gap:14px;align-items:flex-start;padding:22px 22px 18px}.ug-election-completion-mark{display:grid;place-items:center;flex:0 0 auto;width:46px;height:46px;border-radius:50%;background:#dcf7e9;color:#137651;font-size:1.3rem}.ug-election-completion h2{margin:0;color:#164c38;font-size:1.2rem;font-weight:800}.ug-election-completion p{margin:6px 0 0;color:#557268;font-size:.9rem;line-height:1.55}.ug-election-completion-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:0 22px 18px}.ug-election-completion-stat{border:1px solid #dcefe5;border-radius:12px;padding:11px 12px;background:rgba(255,255,255,.75)}.ug-election-completion-stat small{display:block;color:#6b8479;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}.ug-election-completion-stat strong{display:block;margin-top:4px;color:#1d5e47;font-size:.88rem}.ug-election-completion-footer{display:flex;gap:9px;align-items:center;padding:12px 22px;background:#effaf4;border-top:1px solid #dcefe5;color:#467261;font-size:.8rem;line-height:1.45}.ug-election-completion-footer i{color:#2d9a6d}
    .ug-election-layout{display:grid;grid-template-columns:220px minmax(0,1fr);gap:16px;margin-top:18px}
    .ug-election-steps,.ug-election-content{border:1px solid var(--ue-border);border-radius:18px;background:#fff;box-shadow:0 10px 28px rgba(33,75,108,.06)}
    .ug-election-steps{padding:12px;align-self:start;position:sticky;top:16px}.ug-election-steps-title{padding:8px 9px 12px;color:var(--ue-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;font-weight:800}
    .ug-election-step{width:100%;display:flex;align-items:center;gap:9px;border:0;border-radius:11px;padding:11px 10px;background:transparent;color:var(--ue-muted);font-weight:700;text-align:left;cursor:pointer;transition:.18s ease}.ug-election-step:hover{background:var(--ue-soft);color:var(--ue-primary)}.ug-election-step.is-active{background:#eaf6ff;color:var(--ue-primary)}.ug-election-step.is-done{color:#137651}.ug-election-step .ue-step-no{display:grid;place-items:center;width:25px;height:25px;border-radius:50%;background:#edf3f7;color:#688094;font-size:.72rem;flex:0 0 auto}.ug-election-step.is-active .ue-step-no{background:var(--ue-primary-2);color:#fff}.ug-election-step.is-done .ue-step-no{background:#ccefe0;color:#137651}.ug-election-step .ue-step-text{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.84rem}
    .ug-election-content{padding:20px}.ug-election-panel{display:none}.ug-election-panel.is-active{display:block;animation:ue-fade .22s ease}@keyframes ue-fade{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
    .ug-election-panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:16px}.ug-election-panel-head h2{margin:0;font-size:1.25rem;font-weight:800;color:var(--ue-ink)}.ug-election-panel-head p{margin:5px 0 0;color:var(--ue-muted);font-size:.86rem}.ug-election-count{white-space:nowrap;border-radius:999px;background:#f0f7fc;padding:7px 10px;color:var(--ue-primary);font-size:.75rem;font-weight:800}
    .ug-election-candidates{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:13px}.ug-election-candidate{position:relative;border:1px solid var(--ue-border);border-radius:16px;background:#fff;overflow:hidden;transition:.18s ease}.ug-election-candidate:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(29,89,125,.11)}.ug-election-candidate.is-selected{border-color:var(--ue-primary-2);box-shadow:0 0 0 3px rgba(62,161,228,.17);background:#f4fbff}.ug-election-candidate label{display:block;height:100%;padding:14px;cursor:pointer}.ug-election-photo{width:92px;height:92px;display:flex;align-items:center;justify-content:center;margin:0 auto 11px;border-radius:50%;object-fit:cover;border:4px solid #eef6fb;background:#f1f5f8;color:#8ca8ba;font-size:2.35rem}.ug-election-candidate h3{margin:0;text-align:center;font-size:.92rem;line-height:1.3;color:var(--ue-ink)}.ug-election-id{margin:4px 0 0;text-align:center;color:var(--ue-primary);font-size:.77rem;font-weight:800}.ug-election-detail{margin:8px 0 0;text-align:center;color:var(--ue-muted);font-size:.75rem;line-height:1.45;min-height:34px}.ug-election-radio{display:flex;justify-content:center;margin-top:10px}.ug-election-radio input{width:21px;height:21px;accent-color:var(--ue-primary-2)}
    .ug-election-empty{padding:24px 16px;border:1px dashed #c9dbe7;border-radius:14px;background:#f8fbfd;text-align:center;color:var(--ue-muted)}.ug-election-empty i{font-size:1.5rem;color:#94b2c7;margin-bottom:8px}.ug-election-empty p{margin:0;font-size:.85rem}
    .ug-election-actions{display:flex;justify-content:space-between;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--ue-border)}.ug-election-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:11px;padding:11px 17px;font-weight:800;cursor:pointer}.ug-election-btn.primary{background:var(--ue-primary);color:#fff}.ug-election-btn.primary:hover{background:#0e548b;color:#fff}.ug-election-btn.light{background:#eef5f9;color:#416276}.ug-election-btn:disabled{opacity:.65;cursor:not-allowed}
    .ug-election-review{display:grid;gap:8px;margin:16px 0}.ug-election-review-row{display:flex;justify-content:space-between;gap:12px;border:1px solid var(--ue-border);border-radius:11px;padding:10px 12px;background:#fbfdff;font-size:.83rem}.ug-election-review-row span:first-child{color:var(--ue-muted)}.ug-election-review-row strong{color:var(--ue-ink);text-align:right}.ug-election-agree{display:flex;gap:9px;align-items:flex-start;padding:13px;border-radius:12px;background:#fff9ed;border:1px solid #ffe3ae;color:#76531c;font-size:.82rem;line-height:1.45}.ug-election-agree input{width:18px;height:18px;flex:0 0 auto;margin-top:1px;accent-color:var(--ue-primary)}
    .ug-election-results{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:18px}.ug-election-result-card{border:1px solid var(--ue-border);border-radius:16px;background:#fff;padding:15px}.ug-election-result-card h3{margin:0 0 10px;font-size:.98rem;color:var(--ue-ink)}.ug-election-result-row{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid #edf3f7}.ug-election-result-row:first-of-type{border-top:0}.ug-election-result-row img,.ug-election-result-photo-placeholder{width:38px;height:38px;border-radius:50%;object-fit:cover}.ug-election-result-photo-placeholder{display:grid;place-items:center;background:#eef5f9;color:#8ca8ba;font-size:1rem}.ug-election-result-row .ue-result-name{flex:1;font-size:.8rem;font-weight:700}.ug-election-result-row .ue-result-votes{font-size:.8rem;color:var(--ue-primary);font-weight:800}
    .ug-election-spinner{display:none;width:15px;height:15px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:ue-spin .7s linear infinite}@keyframes ue-spin{to{transform:rotate(360deg)}}
    .ug-election-mobile-nav{display:none}
    @media(max-width:800px){.ug-election-shell{padding:10px 8px 28px}.ug-election-hero{border-radius:18px;padding:21px 18px}.ug-election-hero h1{font-size:1.55rem;line-height:1.15}.ug-election-hero p{font-size:.9rem;line-height:1.45}.ug-election-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:15px}.ug-election-pill{justify-content:center;min-width:0;padding:8px 9px;font-size:.7rem;text-align:center}.ug-election-pill:last-child{grid-column:1 / -1}.ug-election-completion-main{padding:18px 16px 15px}.ug-election-completion-grid{padding:0 16px 15px}.ug-election-completion-footer{padding:11px 16px;font-size:.76rem}.ug-election-layout{display:block}.ug-election-steps{display:none}.ug-election-mobile-nav{display:grid;grid-template-columns:38px minmax(0,1fr) 38px;align-items:center;gap:8px;margin-bottom:12px;padding:9px;border:1px solid var(--ue-border);border-radius:14px;background:#f7fbfe}.ug-election-mobile-nav button{display:grid;place-items:center;width:38px;height:38px;border:1px solid #d7e6f0;border-radius:10px;background:#fff;color:var(--ue-primary);cursor:pointer}.ug-election-mobile-nav button:disabled{opacity:.4;cursor:not-allowed}.ug-election-mobile-nav select{width:100%;min-width:0;height:38px;border:1px solid #d7e6f0;border-radius:10px;padding:0 10px;background:#fff;color:var(--ue-ink);font-size:.78rem;font-weight:700}.ug-election-mobile-progress{grid-column:1 / -1;height:4px;border-radius:99px;background:#dfeaf3;overflow:hidden}.ug-election-mobile-progress span{display:block;height:100%;width:0;border-radius:inherit;background:linear-gradient(90deg,var(--ue-primary),var(--ue-primary-2));transition:width .2s ease}.ug-election-content{padding:14px;border-radius:15px}.ug-election-panel-head{display:block}.ug-election-panel-head h2{font-size:1.1rem}.ug-election-count{display:inline-block;margin-top:10px}.ug-election-candidates{grid-template-columns:1fr;gap:9px}.ug-election-candidate label{display:grid;grid-template-columns:64px minmax(0,1fr) 24px;grid-template-areas:'photo name radio' 'photo id radio' 'photo detail radio';align-items:center;column-gap:12px;padding:10px}.ug-election-photo{grid-area:photo;width:64px;height:64px;margin:0}.ug-election-candidate h3{grid-area:name;text-align:left;font-size:.84rem;line-height:1.25}.ug-election-id{grid-area:id;text-align:left;font-size:.7rem}.ug-election-detail{grid-area:detail;text-align:left;min-height:0;margin-top:3px;font-size:.68rem}.ug-election-radio{grid-area:radio;margin:0}.ug-election-radio input{width:20px;height:20px}.ug-election-actions{position:sticky;bottom:7px;background:rgba(255,255,255,.96);margin-left:-4px;margin-right:-4px;padding:12px 4px 4px;z-index:4}.ug-election-btn{padding:10px 12px;font-size:.78rem}.ug-election-results{grid-template-columns:1fr}.ug-election-result-row{padding:8px 0}}
    @media(max-width:390px){.ug-election-meta{grid-template-columns:1fr}.ug-election-pill:last-child{grid-column:auto}.ug-election-content{padding:12px}.ug-election-candidate label{grid-template-columns:58px minmax(0,1fr) 22px;column-gap:9px}.ug-election-photo{width:58px;height:58px}.ug-election-actions{gap:7px}.ug-election-btn{flex:1;padding-left:8px;padding-right:8px}}
</style>
<style>
    .ug-election-choice-summary{margin:0 22px 18px;border:1px solid #dcefe5;border-radius:15px;background:#fff}.ug-election-choice-heading{display:flex;gap:10px;align-items:flex-start;padding:14px 15px;border-bottom:1px solid #e6f2eb}.ug-election-choice-heading>i{margin-top:2px;color:#2d9a6d}.ug-election-choice-heading h3{margin:0;color:#205a45;font-size:.94rem}.ug-election-choice-heading p{margin:3px 0 0;color:#6b8479;font-size:.76rem;line-height:1.4}.ug-election-choice-list{padding:3px 15px}.ug-election-choice-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #eef5f0}.ug-election-choice-row:last-child{border-bottom:0}.ug-election-choice-number{display:grid;place-items:center;flex:0 0 auto;width:25px;height:25px;border-radius:50%;background:#edf8f2;color:#237653;font-size:.72rem;font-weight:800}.ug-election-choice-details{display:grid;gap:2px;min-width:0;flex:1}.ug-election-choice-details small{color:#6b8479;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.ug-election-choice-details strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#1d4939;font-size:.82rem}.ug-election-choice-details span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#728a80;font-size:.7rem}.ug-election-choice-row>i{color:#2d9a6d;font-size:.95rem}
    @media(max-width:800px){.ug-election-choice-summary{margin:0 16px 15px}.ug-election-choice-heading{padding:13px 12px}.ug-election-choice-list{padding:3px 12px}}
</style>
<style>
    .ug-election-closed{margin:18px 0;border:1px solid #d8e5ef;border-radius:20px;background:linear-gradient(145deg,#f7fbff 0%,#fff 72%);box-shadow:0 12px 30px rgba(32,83,119,.08);overflow:hidden}.ug-election-closed-main{display:flex;gap:14px;align-items:flex-start;padding:22px}.ug-election-closed-mark{display:grid;place-items:center;flex:0 0 auto;width:46px;height:46px;border-radius:50%;background:#eaf2f8;color:#3f6d89;font-size:1.2rem}.ug-election-closed h2{margin:0;color:#1c4058;font-size:1.2rem;font-weight:800}.ug-election-closed p{margin:6px 0 0;color:#617b8b;font-size:.9rem;line-height:1.55}.ug-election-closed-footer{display:flex;gap:9px;align-items:center;padding:12px 22px;background:#f1f7fb;border-top:1px solid #dfeaf2;color:#527184;font-size:.8rem;line-height:1.45}.ug-election-closed-footer i{color:#5d8eaa}
    @media(max-width:800px){.ug-election-closed-main{padding:18px 16px}.ug-election-closed-footer{padding:11px 16px;font-size:.76rem}}
</style>

<div class="ug-election-shell" data-election-shell>
    <section class="ug-election-hero">
        <p class="ug-election-kicker"><i class="fas fa-vote-yea"></i> Student elections</p>
        <h1>{{ $category === 'General' ? 'General Election' : $category }}</h1>
        <p>{{ $categoryDescriptions[$category] ?? 'Review the candidates and submit your vote.' }}</p>
        <div class="ug-election-meta">
            <span class="ug-election-pill {{ $open ? 'is-open' : 'is-closed' }}"><i class="fas fa-circle"></i> {{ $statusLabel }}</span>
            <span class="ug-election-pill"><i class="fas fa-layer-group"></i> {{ $positions->count() }} office{{ $positions->count() === 1 ? '' : 's' }}</span>
            @if(!empty($scopeLabel))<span class="ug-election-pill"><i class="fas {{ $scopeIcon }}"></i> {{ $scopeLabel }}</span>@endif
        </div>
    </section>

    @if(!$open && $electionPhase === 'upcoming')
        <section class="ug-election-closed" aria-live="polite">
            <div class="ug-election-closed-main">
                <span class="ug-election-closed-mark"><i class="fas fa-hourglass-start"></i></span>
                <div>
                    <h2>Voting has not opened yet</h2>
                    <p>This ballot is prepared but the election administrator has not opened voting. You will be able to vote when the election begins.</p>
                </div>
            </div>
            <div class="ug-election-closed-footer"><i class="fas fa-bell"></i><span>Please check back when the election is announced as open.</span></div>
        </section>
    @elseif(!$open)
        <section class="ug-election-closed" aria-live="polite">
            <div class="ug-election-closed-main">
                <span class="ug-election-closed-mark"><i class="fas fa-lock"></i></span>
                <div>
                    <h2>Voting has ended</h2>
                    <p>This election ballot is now closed, so no further votes can be submitted.</p>
                </div>
            </div>
            <div class="ug-election-closed-footer"><i class="fas {{ $visibleCandidates->count() ? 'fa-chart-bar' : 'fa-clock' }}"></i><span>{{ $visibleCandidates->count() ? 'Published results are shown below.' : 'Results will appear here when they are published by the election administrator.' }}</span></div>
        </section>
    @elseif($hasVoted)
        <section class="ug-election-completion" aria-live="polite">
            <div class="ug-election-completion-main">
                <span class="ug-election-completion-mark"><i class="fas fa-check"></i></span>
                <div>
                    <h2>Your vote has been recorded</h2>
                    <p>Thank you for taking part in the {{ $category === 'General' ? 'general election' : strtolower($category) }}. Your ballot was submitted successfully and cannot be changed.</p>
                </div>
            </div>
            <div class="ug-election-completion-grid">
                <div class="ug-election-completion-stat"><small>Ballot status</small><strong>Submitted securely</strong></div>
                <div class="ug-election-completion-stat"><small>Next update</small><strong>Results after voting closes</strong></div>
            </div>
            @if($submittedChoices->count())
                <div class="ug-election-choice-summary">
                    <div class="ug-election-choice-heading"><i class="fas fa-list-check"></i><div><h3>Your submitted choices</h3><p>This is a read-only copy of the choices recorded for your ballot.</p></div></div>
                    <div class="ug-election-choice-list">
                        @foreach($submittedChoices as $choice)
                            <div class="ug-election-choice-row"><span class="ug-election-choice-number">{{ $loop->iteration }}</span><div class="ug-election-choice-details"><small>{{ $choice->position }}</small><strong>{{ $choice->name ?: 'Candidate' }}</strong><span>{{ $choice->candidate }}</span></div><i class="fas fa-check-circle"></i></div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="ug-election-completion-footer"><i class="fas fa-clock"></i><span>Keep this page bookmarked. Published results will appear here automatically after the election is closed.</span></div>
        </section>
    @elseif(!$eligibility)
        <div class="ug-election-notice"><i class="fas fa-exclamation-circle"></i><div><strong>You cannot vote in this category yet</strong><p>{{ $eligibilityMessage }}</p></div></div>
    @endif

    @if($canVote)
        <form action="/voting" method="POST" data-election-form>
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">
            <div class="ug-election-layout">
                <aside class="ug-election-steps" aria-label="Election offices">
                    <div class="ug-election-steps-title">Your ballot</div>
                    @foreach($positions as $index => $pos)
                        @php $rows = $visibleCandidates->where('position', $pos->position); @endphp
                        <button type="button" class="ug-election-step {{ $index === 0 ? 'is-active' : '' }}" data-election-step="panel-{{ $pos->id }}" data-required="{{ $rows->count() ? '1' : '0' }}"><span class="ue-step-no">{{ $index + 1 }}</span><span class="ue-step-text">{{ $pos->position }}</span></button>
                    @endforeach
                    <button type="button" class="ug-election-step" data-election-step="panel-submit"><span class="ue-step-no"><i class="fas fa-check"></i></span><span class="ue-step-text">Review & submit</span></button>
                </aside>

                <section class="ug-election-content">
                    <div class="ug-election-mobile-nav" data-election-mobile-nav aria-label="Ballot progress">
                        <button type="button" data-election-mobile-prev aria-label="Previous office"><i class="fas fa-chevron-left"></i></button>
                        <select data-election-mobile-select aria-label="Choose an office">
                            @foreach($positions as $index => $pos)
                                <option value="panel-{{ $pos->id }}">{{ $index + 1 }} of {{ $positions->count() }} · {{ $pos->position }}</option>
                            @endforeach
                            <option value="panel-submit">Review and submit</option>
                        </select>
                        <button type="button" data-election-mobile-next aria-label="Next office"><i class="fas fa-chevron-right"></i></button>
                        <div class="ug-election-mobile-progress" aria-hidden="true"><span data-election-mobile-progress></span></div>
                    </div>
                    @foreach($positions as $index => $pos)
                        @php $rows = $visibleCandidates->where('position', $pos->position); @endphp
                        <div class="ug-election-panel {{ $index === 0 ? 'is-active' : '' }}" id="panel-{{ $pos->id }}" data-election-panel>
                            <div class="ug-election-panel-head"><div><h2>{{ $pos->position }}</h2><p>Select one candidate for this office.</p></div><span class="ug-election-count">{{ $rows->count() }} candidate{{ $rows->count() === 1 ? '' : 's' }}</span></div>
                            @if($rows->count())
                                <div class="ug-election-candidates">
                                    @foreach($rows as $row)
                                        <div class="ug-election-candidate" id="candidate-card-{{ $row->id }}">
                                            <label for="candidate-{{ $row->id }}">
                                                @if(!empty($row->picture) && file_exists(public_path('storage/picture/' . $row->picture)))
                                                    <img class="ug-election-photo" src="{{ asset('storage/picture/' . $row->picture) }}" alt="{{ $row->name }}">
                                                @else
                                                    <span class="ug-election-photo" aria-label="No photograph available"><i class="fas fa-user"></i></span>
                                                @endif
                                                <h3>{{ $row->name ?: 'Candidate' }}</h3>
                                                <p class="ug-election-id">{{ $row->candidate }}</p>
                                                <p class="ug-election-detail">{{ $row->program_title ?: ($row->program ?: 'Student representative') }}
                                                    @if($row->level)<br>{{ $row->level }} level @endif
                                                </p>
                                                <span class="ug-election-radio"><input id="candidate-{{ $row->id }}" type="radio" name="{{ $pos->position }}" value="{{ $row->candidate }}" data-candidate-name="{{ $row->name }}" data-office="{{ $pos->position }}"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="ug-election-empty"><i class="fas fa-user-slash"></i><p>No candidate has been published for this office.</p></div>
                            @endif
                            <div class="ug-election-actions"><button type="button" class="ug-election-btn light" data-election-prev {{ $index === 0 ? 'disabled' : '' }}><i class="fas fa-arrow-left"></i> Back</button><button type="button" class="ug-election-btn primary" data-election-next>{{ $index === $positions->count()-1 ? 'Review ballot' : 'Next office' }} <i class="fas fa-arrow-right"></i></button></div>
                        </div>
                    @endforeach

                    @if($positions->isEmpty())
                        <div class="ug-election-empty"><i class="fas fa-inbox"></i><p>No voting office is currently available for this category.</p></div>
                    @endif
                    <div class="ug-election-panel {{ $positions->isEmpty() ? 'is-active' : '' }}" id="panel-submit" data-election-panel><div class="ug-election-panel-head"><div><h2>Review your ballot</h2><p>Check your selections carefully. You cannot change them after submission.</p></div><span class="ug-election-count">Final step</span></div><div class="ug-election-review" data-election-review><div class="ug-election-empty"><i class="fas fa-clipboard-check"></i><p>Your selections will appear here.</p></div></div><label class="ug-election-agree"><input type="checkbox" name="agreement" value="1" required><span>I confirm that these are my choices and understand that my vote cannot be changed after submission.</span></label><div class="ug-election-actions"><button type="button" class="ug-election-btn light" data-election-prev><i class="fas fa-arrow-left"></i> Back</button><button type="submit" class="ug-election-btn primary" data-election-submit><span class="ug-election-spinner"></span><span>Submit vote</span> <i class="fas fa-paper-plane"></i></button></div></div>
                </section>
            </div>
        </form>
    @elseif(!$open && $visibleCandidates->count())
        <div class="ug-election-results">
            @foreach($positions as $pos)
                @php $rows = $visibleCandidates->where('position', $pos->position)->sortByDesc('vote'); @endphp
                @if($rows->count())
                    <article class="ug-election-result-card">
                        <h3>{{ $pos->position }}</h3>
                        @foreach($rows as $row)
                            <div class="ug-election-result-row">
                                @if(!empty($row->picture) && file_exists(public_path('storage/picture/' . $row->picture)))
                                    <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="">
                                @else
                                    <span class="ug-election-result-photo-placeholder" aria-label="No photograph available"><i class="fas fa-user"></i></span>
                                @endif
                                <span class="ue-result-name">{{ $row->name ?: $row->candidate }}</span><span class="ue-result-votes">{{ number_format((int)$row->vote) }}</span>
                            </div>
                        @endforeach
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</div>

<script>
(() => { const shell=document.querySelector('[data-election-shell]'); if(!shell)return; const form=shell.querySelector('[data-election-form]'),steps=[...shell.querySelectorAll('[data-election-step]')],panels=[...shell.querySelectorAll('[data-election-panel]')]; let current=Math.max(0,steps.findIndex(s=>s.classList.contains('is-active'))); const review=()=>{const box=shell.querySelector('[data-election-review]');if(!box)return;const selected=[...shell.querySelectorAll('input[type="radio"]:checked')];box.innerHTML=selected.length?selected.map(i=>`<div class="ug-election-review-row"><span>${i.dataset.office||'Office'}</span><strong>${i.dataset.candidateName||i.value}</strong></div>`).join(''):'<div class="ug-election-empty"><i class="fas fa-clipboard-check"></i><p>No selections have been made yet.</p></div>';}; const done=()=>steps.forEach(s=>{const p=document.getElementById(s.dataset.electionStep);if(p&&s.dataset.electionStep!=='panel-submit')s.classList.toggle('is-done',!!p.querySelector('input:checked'));}); const activate=i=>{current=Math.max(0,Math.min(i,steps.length-1));steps.forEach((s,n)=>s.classList.toggle('is-active',n===current));panels.forEach(p=>p.classList.toggle('is-active',p.id===steps[current]?.dataset.electionStep));if(steps[current]?.dataset.electionStep==='panel-submit')review();shell.scrollIntoView({behavior:'smooth',block:'start'});}; steps.forEach((s,i)=>s.addEventListener('click',()=>activate(i))); shell.querySelectorAll('[data-election-next]').forEach(b=>b.addEventListener('click',()=>{const p=panels.find(x=>x.classList.contains('is-active')),s=steps[current];if(s?.dataset.required==='1'&&!p?.querySelector('input:checked')){const input=p?.querySelector('input');if(input)input.focus();const hint=p?.querySelector('.ug-election-panel-head p');if(hint)hint.textContent='Please select one candidate before continuing.';return;}done();activate(current+1);})); shell.querySelectorAll('[data-election-prev]').forEach(b=>b.addEventListener('click',()=>{done();activate(current-1);})); shell.querySelectorAll('input[type="radio"]').forEach(i=>i.addEventListener('change',()=>{shell.querySelectorAll('.ug-election-candidate').forEach(c=>c.classList.remove('is-selected'));i.closest('.ug-election-candidate')?.classList.add('is-selected');done();review();})); form?.addEventListener('submit',e=>{for(const s of steps){if(s.dataset.required!=='1'||s.dataset.electionStep==='panel-submit')continue;const p=document.getElementById(s.dataset.electionStep);if(!p?.querySelector('input:checked')){e.preventDefault();activate(steps.indexOf(s));p?.querySelector('input')?.focus();return;}}if(!form.querySelector('input[type="radio"]:checked')){e.preventDefault();activate(steps.length-1);review();return;}const b=form.querySelector('[data-election-submit]');if(b){b.disabled=true;b.querySelector('.ug-election-spinner').style.display='inline-block';b.querySelector('span:last-of-type').textContent='Submitting…';}});done(); })();
</script>
<script>
(() => {
    const shell = document.querySelector('[data-election-shell]');
    if (!shell) return;
    const steps = [...shell.querySelectorAll('[data-election-step]')];
    const select = shell.querySelector('[data-election-mobile-select]');
    const previous = shell.querySelector('[data-election-mobile-prev]');
    const next = shell.querySelector('[data-election-mobile-next]');
    const progress = shell.querySelector('[data-election-mobile-progress]');
    if (!select || !steps.length) return;

    const sync = () => {
        const active = steps.findIndex(step => step.classList.contains('is-active'));
        const index = active < 0 ? 0 : active;
        select.value = steps[index].dataset.electionStep;
        if (previous) previous.disabled = index === 0;
        if (next) next.disabled = index === steps.length - 1;
        if (progress) progress.style.width = `${Math.round((index / Math.max(steps.length - 1, 1)) * 100)}%`;
    };

    select.addEventListener('change', event => {
        const target = steps.find(step => step.dataset.electionStep === event.target.value);
        if (target) target.click();
        window.setTimeout(sync, 0);
    });
    previous?.addEventListener('click', () => {
        const active = steps.find(step => step.classList.contains('is-active'));
        const index = steps.indexOf(active);
        if (index > 0) steps[index - 1].click();
        window.setTimeout(sync, 0);
    });
    next?.addEventListener('click', () => {
        const active = steps.find(step => step.classList.contains('is-active'));
        const index = steps.indexOf(active);
        if (index >= 0) {
            const panel = document.getElementById(active.dataset.electionStep);
            panel?.querySelector('[data-election-next]')?.click();
        }
        window.setTimeout(sync, 0);
    });
    steps.forEach(step => new MutationObserver(sync).observe(step, { attributes: true, attributeFilter: ['class'] }));
    sync();
})();
</script>
