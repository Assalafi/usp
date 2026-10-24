@php
    $adminPage = $page ?? 'election settings';
    $isSettings = $adminPage === 'election settings';
    $isPositions = $adminPage === 'election positions';
    $isCandidates = in_array($adminPage, ['election candidates', 'election general', 'election faculty', 'election hostel', 'election lga'], true);
    $isVotes = $adminPage === 'election votes';
    $category = $category ?? null;
    $records = collect($data ?? []);
    if ($isCandidates && $category && $adminPage !== 'election candidates') {
        $records = $records->where('category', $category)->values();
    }
    $positions = collect($poss ?? ($positions ?? []));
    if ($isCandidates && $category && $adminPage !== 'election candidates') {
        $positions = $positions->where('category', $category)->values();
    }
    $status = strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value'));
    $candidateCount = $isCandidates ? $records->count() : DB::table('election_candidates')->count();
    $voteCount = $isVotes ? $records->count() : DB::table('election_votes')->count();
    $voterCount = DB::table('election_votes')->distinct('username')->count('username');
    $officeTabs = collect($positions)->pluck('position')->filter()->unique()->values();
    $officeTabs = $officeTabs->concat($records->pluck('position')->filter()->unique())->unique()->values();
    $entityField = $adminPage === 'election faculty' ? 'faculty' : ($adminPage === 'election hostel' ? 'hostel' : null);
    $entityLabel = $adminPage === 'election faculty' ? 'Faculty / college' : ($adminPage === 'election hostel' ? 'Hostel' : null);
    $entityTabs = $entityField
        ? $records->pluck($entityField)->filter(fn ($value) => trim((string) $value) !== '')->map(fn ($value) => (string) $value)->unique()->sort()->values()
        : collect();
    $entityLabels = $entityField === 'faculty'
        ? DB::table('faculty')->whereIn('code', $entityTabs->all())->pluck('title', 'code')
        : collect();
@endphp

<style>
    .ea-shell{--ea-blue:#1769aa;--ea-blue2:#3ea1e4;--ea-ink:#17344b;--ea-muted:#6b8190;--ea-line:#dce9f1;--ea-soft:#f4f9fc;max-width:1240px;margin:0 auto;padding:18px 12px 46px;color:var(--ea-ink)}
    .ea-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:25px 27px;border-radius:22px;background:linear-gradient(135deg,#0f5e9b,#268dcc 58%,#3ea1e4);color:#fff;box-shadow:0 16px 38px rgba(21,93,139,.17)}.ea-kicker{margin:0 0 6px;opacity:.78;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;font-weight:800}.ea-hero h1{margin:0;color:#fff;font-size:clamp(1.35rem,3vw,2rem);font-weight:800}.ea-hero p{margin:7px 0 0;color:rgba(255,255,255,.87);font-size:.9rem}.ea-status{white-space:nowrap;border-radius:999px;padding:9px 13px;background:rgba(255,255,255,.16);font-size:.75rem;font-weight:800}.ea-status.open{background:#dff8ea;color:#146842}.ea-status.closed{background:#fff3df;color:#8a510d}
    .ea-tabs{display:flex;gap:7px;overflow:auto;margin:16px 0;padding:5px 1px;scrollbar-width:none}.ea-tabs::-webkit-scrollbar{display:none}.ea-tab{flex:0 0 auto;padding:9px 13px;border:1px solid var(--ea-line);border-radius:999px;background:#fff;color:#557286;text-decoration:none;font-size:.78rem;font-weight:800;transition:.18s}.ea-tab:hover,.ea-tab.active{border-color:#a9d7ef;background:#eaf6fd;color:var(--ea-blue)}
    .ea-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;margin-bottom:16px}.ea-stat{padding:15px;border:1px solid var(--ea-line);border-radius:15px;background:#fff;box-shadow:0 7px 20px rgba(30,83,113,.05)}.ea-stat small{display:block;color:var(--ea-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}.ea-stat strong{display:block;margin-top:6px;color:var(--ea-ink);font-size:1.3rem}.ea-stat span{display:block;margin-top:3px;color:#7690a0;font-size:.72rem}
    .ea-panel{border:1px solid var(--ea-line);border-radius:18px;background:#fff;box-shadow:0 9px 26px rgba(30,83,113,.06);overflow:hidden}.ea-panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e8f0f5}.ea-panel-head h2{margin:0;color:var(--ea-ink);font-size:1rem;font-weight:800}.ea-panel-head p{margin:4px 0 0;color:var(--ea-muted);font-size:.78rem}.ea-panel-body{padding:15px 18px}.ea-actions{display:flex;gap:8px;align-items:center}.ea-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:0;border-radius:10px;padding:9px 13px;background:var(--ea-blue);color:#fff;font-size:.77rem;font-weight:800;cursor:pointer;text-decoration:none}.ea-btn:hover{background:#0d548c;color:#fff}.ea-btn.light{background:#edf5f9;color:#426276}.ea-btn.danger{background:#fff0f1;color:#aa3442}.ea-btn.sm{padding:7px 9px;font-size:.72rem}.ea-filter{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:13px}.ea-filter input,.ea-filter select,.ea-form input,.ea-form select{width:100%;min-height:40px;border:1px solid #d7e6ef;border-radius:9px;padding:0 11px;color:var(--ea-ink);background:#fff;font-size:.8rem}.ea-filter .ea-search{flex:1 1 260px}.ea-filter select{flex:0 1 190px}.ea-table-wrap{overflow:auto}.ea-table{width:100%;border-collapse:separate;border-spacing:0;min-width:650px}.ea-table th{padding:10px 11px;background:#f3f8fb;color:#688091;font-size:.68rem;text-align:left;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}.ea-table td{padding:11px;border-bottom:1px solid #edf3f6;color:#36556a;font-size:.78rem;vertical-align:middle}.ea-table tr:last-child td{border-bottom:0}.ea-table tr[data-hidden="1"]{display:none}.ea-muted{color:var(--ea-muted)}.ea-badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 8px;background:#edf5fa;color:#416d87;font-size:.68rem;font-weight:800}.ea-badge.green{background:#e4f8ed;color:#147249}.ea-badge.amber{background:#fff4e1;color:#8a5a10}.ea-person{display:flex;align-items:center;gap:9px;min-width:170px}.ea-avatar{display:grid;place-items:center;width:34px;height:34px;flex:0 0 auto;border-radius:50%;object-fit:cover;background:#eaf3f8;color:#7f9aab;font-size:.9rem}.ea-person strong{display:block;color:var(--ea-ink);font-size:.78rem}.ea-person small{display:block;margin-top:2px;color:var(--ea-muted);font-size:.68rem}.ea-empty{padding:35px 16px;text-align:center;color:var(--ea-muted)}.ea-empty i{display:block;margin-bottom:8px;color:#95b0bf;font-size:1.6rem}.ea-drawer{position:fixed;inset:0;z-index:1060;display:none;background:rgba(9,35,53,.45);padding:16px}.ea-drawer.open{display:flex;justify-content:flex-end}.ea-dialog{width:min(460px,100%);height:100%;overflow:auto;border-radius:18px;background:#fff;box-shadow:0 20px 55px rgba(7,42,68,.25)}.ea-dialog-head{display:flex;justify-content:space-between;align-items:center;padding:17px 19px;border-bottom:1px solid var(--ea-line)}.ea-dialog-head h3{margin:0;font-size:1rem;color:var(--ea-ink)}.ea-close{border:0;background:transparent;color:#6d8594;font-size:1.1rem;cursor:pointer}.ea-form{display:grid;gap:12px;padding:18px}.ea-field{display:grid;gap:5px}.ea-field label{color:#557084;font-size:.72rem;font-weight:800}.ea-form-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:5px}.ea-toast{position:fixed;right:18px;bottom:18px;z-index:1100;display:none;max-width:340px;padding:12px 15px;border-radius:11px;background:#163f59;color:#fff;box-shadow:0 12px 28px rgba(12,57,84,.25);font-size:.8rem}.ea-toast.show{display:block}.ea-toast.error{background:#963d4a}
    @media(max-width:800px){.ea-shell{padding:10px 8px 32px}.ea-hero{display:block;padding:20px 18px;border-radius:18px}.ea-status{display:inline-flex;margin-top:15px}.ea-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.ea-stat{padding:12px}.ea-stat strong{font-size:1.12rem}.ea-panel-head{display:block;padding:14px}.ea-panel-head .ea-actions{margin-top:11px}.ea-panel-body{padding:12px}.ea-filter{display:grid;grid-template-columns:1fr 1fr}.ea-filter .ea-search{grid-column:1 / -1}.ea-filter select{width:100%}.ea-table{min-width:590px}.ea-dialog{width:100%;border-radius:15px}.ea-drawer{padding:8px}}
</style>
<style>
    .ea-btn.is-loading{opacity:.72;pointer-events:none;position:relative;padding-right:34px}.ea-btn.is-loading:after{content:'';position:absolute;right:11px;width:13px;height:13px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:ea-spin .7s linear infinite}@keyframes ea-spin{to{transform:rotate(360deg)}}
    .ea-form-actions .ea-btn:not(.light){background:#1769aa!important;color:#fff!important;text-shadow:none!important;border:0!important}.ea-form-actions .ea-btn:not(.light):hover{background:#0d548c!important;color:#fff!important}.ea-form-actions .ea-btn:not(.light) [data-election-submit-label]{color:#fff!important;visibility:visible!important;opacity:1!important}
    .ea-dialog{width:min(520px,100%);border:1px solid #d8e8f1}.ea-dialog-head{padding:20px 21px;background:linear-gradient(135deg,#f1f9fd,#fff);align-items:flex-start}.ea-dialog-title-wrap{display:flex;align-items:flex-start;gap:12px}.ea-dialog-icon{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#dff2fc;color:#1769aa;font-size:1rem}.ea-dialog-head h3{font-size:1.05rem}.ea-dialog-head p{margin:4px 0 0;color:#718997;font-size:.74rem;line-height:1.45}.ea-form{gap:14px;padding:20px 21px}.ea-field{gap:7px}.ea-field label{font-size:.74rem;color:#355b70}.ea-field label::after{content:' *';color:#c3545f}.ea-form input,.ea-form select{min-height:44px;border-radius:11px;box-shadow:0 2px 7px rgba(35,91,122,.04);transition:border-color .16s,box-shadow .16s}.ea-form input:focus,.ea-form select:focus{outline:0;border-color:#59add6;box-shadow:0 0 0 3px rgba(62,161,228,.13)}.ea-form-actions{position:sticky;bottom:0;margin:7px -21px -20px;padding:13px 21px;background:rgba(255,255,255,.95);border-top:1px solid #e5eef3}.ea-form-actions .ea-btn{min-height:42px;padding-inline:17px}.ea-form-actions .ea-btn.light{border:1px solid #d7e7ef;background:#f6fafc}
    .ea-office-tabs{display:flex;gap:8px;overflow:auto;padding:2px 1px 12px;scrollbar-width:none}.ea-office-tabs::-webkit-scrollbar{display:none}.ea-office-tab{display:inline-flex;align-items:center;gap:7px;flex:0 0 auto;padding:9px 12px;border:1px solid #dbeaf2;border-radius:12px;background:#f8fbfd;color:#5c7686;font-size:.74rem;font-weight:800;cursor:pointer;transition:.18s}.ea-office-tab span{display:inline-grid;place-items:center;min-width:21px;height:21px;border-radius:999px;background:#e7f2f8;color:#4c7890;font-size:.66rem}.ea-office-tab:hover,.ea-office-tab.active{border-color:#58add4;background:#e6f5fc;color:#12669a;box-shadow:0 4px 12px rgba(47,136,180,.12)}.ea-office-tab.active span{background:#1769aa;color:#fff}.ea-office-caption{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:2px 0 10px;color:#6a8391;font-size:.73rem}.ea-office-caption strong{color:#234e66;font-size:.82rem}.ea-entity-filter{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 13px;padding:10px 12px;border:1px solid #dcecf4;border-radius:13px;background:#f8fcfe}.ea-entity-caption{display:flex;align-items:center;gap:7px;margin-right:3px;color:#6a8391;font-size:.72rem}.ea-entity-caption strong{color:#234e66;font-size:.78rem}.ea-entity-caption span{color:#8098a5}.ea-entity-tabs{display:flex;gap:6px;overflow:auto;flex:1;scrollbar-width:none}.ea-entity-tabs::-webkit-scrollbar{display:none}.ea-entity-tab{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;padding:7px 10px;border:1px solid #d6e7f0;border-radius:999px;background:#fff;color:#527388;font-size:.71rem;font-weight:800;cursor:pointer;transition:.16s}.ea-entity-tab:hover,.ea-entity-tab.active{border-color:#57acd5;background:#e3f4fc;color:#11689b}.ea-entity-tab span{display:inline-grid;place-items:center;min-width:19px;height:19px;padding:0 4px;border-radius:999px;background:#edf5f9;color:#5f7c8b;font-size:.63rem}.ea-entity-tab.active span{background:#1769aa;color:#fff}
    .ea-panel-body:has(>.ea-office-tabs){display:grid;grid-template-columns:minmax(185px,225px) minmax(0,1fr);column-gap:20px;align-items:start}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-caption{grid-column:1;grid-row:1;display:flex;flex-direction:column;align-items:flex-start;gap:4px;margin:0 0 8px}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-tabs{grid-column:1;grid-row:2;display:flex;flex-direction:column;align-items:stretch;overflow:visible;padding:0 0 4px;border-right:1px solid #e5eff4;padding-right:14px}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-tabs .ea-office-tab{justify-content:flex-start;width:100%;text-align:left;border-radius:10px}.ea-panel-body:has(>.ea-office-tabs)>.ea-filter{grid-column:2;grid-row:1;margin-bottom:12px}.ea-panel-body:has(>.ea-office-tabs)>.ea-table-wrap{grid-column:2;grid-row:2;min-width:0}.ea-panel-body:has(>.ea-office-tabs):has(>.ea-entity-filter)>.ea-entity-filter{grid-column:2;grid-row:2;margin-bottom:12px}.ea-panel-body:has(>.ea-office-tabs):has(>.ea-entity-filter)>.ea-table-wrap{grid-column:2;grid-row:3}
    @media(max-width:800px){.ea-panel-body:has(>.ea-office-tabs){display:block}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-caption{display:flex;flex-direction:row;align-items:center;margin:2px 0 9px}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-tabs{display:flex;flex-direction:row;align-items:stretch;overflow:auto;padding:2px 1px 12px;border-right:0}.ea-panel-body:has(>.ea-office-tabs)>.ea-office-tabs .ea-office-tab{width:auto;justify-content:center;text-align:center}}
    @media(max-width:560px){.ea-entity-filter{display:block;padding:10px}.ea-entity-caption{justify-content:space-between;margin-bottom:8px}.ea-entity-tabs{padding-bottom:2px}.ea-entity-tab{font-size:.68rem}}
</style>

<div class="ea-shell" id="election-admin-root" data-election-admin data-election-page="{{ $adminPage }}">
    <section class="ea-hero">
        <div><p class="ea-kicker"><i class="fas fa-shield-halved"></i> Election administration</p><h1>{{ ucwords(str_replace('election ', '', $adminPage)) }}</h1><p>Manage election settings, offices, candidates, votes, and live results from one workspace.</p></div>
        <span class="ea-status {{ $status === 'OPEN' ? 'open' : 'closed' }}"><i class="fas fa-circle"></i> {{ $status === 'OPEN' ? 'Voting open' : 'Voting closed' }}</span>
    </section>

    <nav class="ea-tabs" aria-label="Election administration sections">
        @foreach(['election settings' => 'Settings','election positions' => 'Offices','election candidates' => 'Candidates','election votes' => 'Votes','election live' => 'Live results'] as $tab => $label)
            <a class="ea-tab {{ $adminPage === $tab ? 'active' : '' }}" href="{{ url($tab) }}" data-election-tab><i class="fas {{ $tab === 'election settings' ? 'fa-sliders' : ($tab === 'election positions' ? 'fa-briefcase' : ($tab === 'election candidates' ? 'fa-users' : ($tab === 'election votes' ? 'fa-check-double' : 'fa-chart-column'))) }}"></i> {{ $label }}</a>
        @endforeach
    </nav>

    <div class="ea-stats">
        <div class="ea-stat"><small>Election status</small><strong>{{ $status === 'OPEN' ? 'Open' : 'Closed' }}</strong><span>Current voting switch</span></div>
        <div class="ea-stat"><small>Registered candidates</small><strong>{{ number_format($candidateCount) }}</strong><span>Across all offices</span></div>
        <div class="ea-stat"><small>Votes recorded</small><strong>{{ number_format($voteCount) }}</strong><span>Submitted selections</span></div>
        <div class="ea-stat"><small>Voters represented</small><strong>{{ number_format($voterCount) }}</strong><span>Unique voters</span></div>
    </div>

    @if($isSettings)
        <section class="ea-panel">
            <div class="ea-panel-head"><div><h2>Election settings</h2><p>Control the voting switch and election-wide rules.</p></div><div class="ea-actions"><button class="ea-btn" type="button" data-election-create><i class="fas fa-plus"></i> Add setting</button></div></div>
            <div class="ea-panel-body"><div class="ea-filter"><input class="ea-search" type="search" placeholder="Search settings" data-election-search></div><div class="ea-table-wrap"><table class="ea-table"><thead><tr><th>Setting</th><th>Value</th><th>Action</th></tr></thead><tbody data-election-list>@forelse($records as $row)<tr data-row data-search="{{ strtolower($row->description.' '.$row->value) }}"><td><strong>{{ $row->description }}</strong></td><td><span class="ea-badge {{ strtoupper($row->value) === 'OPEN' ? 'green' : '' }}">{{ $row->value }}</span></td><td><button type="button" class="ea-btn light sm" data-election-edit data-id="{{ $row->id }}" data-description="{{ $row->description }}" data-value="{{ $row->value }}"><i class="fas fa-pen"></i> Edit</button> <button type="button" class="ea-btn danger sm" data-election-delete data-id="{{ $row->id }}"><i class="fas fa-trash"></i></button></td></tr>@empty<tr><td colspan="3"><div class="ea-empty"><i class="fas fa-sliders"></i>No settings found.</div></td></tr>@endforelse</tbody></table></div></div>
        </section>
    @elseif($isPositions)
        <section class="ea-panel">
            <div class="ea-panel-head"><div><h2>Election offices</h2><p>Define the offices voters will see and the order in which they appear.</p></div><div class="ea-actions"><button class="ea-btn" type="button" data-election-create><i class="fas fa-plus"></i> Add office</button></div></div>
            <div class="ea-panel-body"><div class="ea-filter"><input class="ea-search" type="search" placeholder="Search offices" data-election-search></div><div class="ea-table-wrap"><table class="ea-table"><thead><tr><th>Order</th><th>Office</th><th>Category</th><th>Gender</th><th>Action</th></tr></thead><tbody data-election-list>@forelse($records as $row)<tr data-row data-search="{{ strtolower($row->position.' '.$row->category.' '.$row->gender) }}" data-category="{{ $row->category }}"><td><span class="ea-badge">{{ $row->order }}</span></td><td><strong>{{ $row->position }}</strong></td><td>{{ $row->category }}</td><td>{{ $row->gender }}</td><td><button type="button" class="ea-btn light sm" data-election-edit data-id="{{ $row->id }}" data-position="{{ $row->position }}" data-category="{{ $row->category }}" data-order="{{ $row->order }}" data-gender="{{ $row->gender }}"><i class="fas fa-pen"></i> Edit</button> <button type="button" class="ea-btn danger sm" data-election-delete data-id="{{ $row->id }}"><i class="fas fa-trash"></i></button></td></tr>@empty<tr><td colspan="5"><div class="ea-empty"><i class="fas fa-briefcase"></i>No offices found.</div></td></tr>@endforelse</tbody></table></div></div>
        </section>
    @elseif($isVotes)
        <section class="ea-panel">
            <div class="ea-panel-head"><div><h2>Vote register</h2><p>Review submitted selections with fast client-side filtering.</p></div></div>
            <div class="ea-panel-body">@if($officeTabs->isNotEmpty())<div class="ea-office-caption"><span><strong data-office-title>All offices</strong> <span data-office-count>{{ $records->count() }} records</span></span><span class="ea-muted">Select an office to focus the vote register</span></div><div class="ea-office-tabs" role="tablist" aria-label="Vote offices"><button type="button" class="ea-office-tab active" data-office-filter=""><span>All</span> Offices</button>@foreach($officeTabs as $office)<button type="button" class="ea-office-tab" data-office-filter="{{ strtolower($office) }}"><span>{{ $records->where('position', $office)->count() }}</span> {{ $office }}</button>@endforeach</div>@endif<div class="ea-filter"><input class="ea-search" type="search" placeholder="Search voter, office, or candidate" data-election-search></div><div class="ea-table-wrap"><table class="ea-table"><thead><tr><th>Voter</th><th>Category</th><th>Office</th><th>Candidate</th><th>Type</th><th>Action</th></tr></thead><tbody data-election-list>@forelse($records as $row)<tr data-row data-search="{{ strtolower($row->username.' '.$row->category.' '.$row->position.' '.$row->candidate) }}" data-category="{{ $row->category }}" data-office="{{ strtolower($row->position ?? '') }}"><td><strong>{{ $row->username }}</strong></td><td>{{ $row->category }}</td><td>{{ $row->position }}</td><td>{{ $row->candidate }}</td><td><span class="ea-badge">{{ $row->acc_type }}</span></td><td><button type="button" class="ea-btn danger sm" data-election-delete data-id="{{ $row->id }}"><i class="fas fa-trash"></i></button></td></tr>@empty<tr><td colspan="6"><div class="ea-empty"><i class="fas fa-check-double"></i>No votes recorded.</div></td></tr>@endforelse</tbody></table></div></div>
        </section>
    @else
        <section class="ea-panel">
<div class="ea-panel-head"><div><h2>{{ $isCandidates ? ($category ? $category.' candidates' : 'Candidate register') : 'Live election results' }}</h2><p>{{ $isCandidates ? 'Search and review candidates by office, category, and voter type.' : 'Monitor the current vote totals as they are recorded.' }}</p></div><div class="ea-actions">@if($isCandidates)<button class="ea-btn" type="button" data-election-create><i class="fas fa-user-plus"></i> Add candidate</button>@endif</div></div>
            <div class="ea-panel-body">
                @if($officeTabs->isNotEmpty())<div class="ea-office-caption"><span><strong data-office-title>All offices</strong> <span data-office-count>{{ $records->count() }} records</span></span><span class="ea-muted">Select an office to focus the ballot</span></div><div class="ea-office-tabs" role="tablist" aria-label="Election offices"><button type="button" class="ea-office-tab active" data-office-filter=""><span>All</span> Offices</button>@foreach($officeTabs as $office)<button type="button" class="ea-office-tab" data-office-filter="{{ strtolower($office) }}"><span>{{ $records->where('position', $office)->count() }}</span> {{ $office }}</button>@endforeach</div>@endif
                @if($entityTabs->isNotEmpty())<div class="ea-entity-filter"><div class="ea-entity-caption"><strong>{{ $entityLabel }}</strong><span>{{ $entityTabs->count() }} with candidates</span></div><div class="ea-entity-tabs" role="tablist" aria-label="{{ $entityLabel }} candidates"><button type="button" class="ea-entity-tab active" data-entity-filter=""><span>All</span> {{ $entityLabel }}</button>@foreach($entityTabs as $entity)<button type="button" class="ea-entity-tab" data-entity-filter="{{ strtolower($entity) }}"><span>{{ $records->where($entityField, $entity)->count() }}</span> {{ $entityLabels->get($entity, $entity) }}@if($entityField === 'faculty' && $entityLabels->has($entity)) <small>({{ $entity }})</small>@endif</button>@endforeach</div></div>@endif
                <div class="ea-filter"><input class="ea-search" type="search" placeholder="Search candidate, office, or ID" data-election-search><select data-election-filter="acc_type" aria-label="Filter by voter type"><option value="Hide" selected>Hide</option><option value="Student">Student</option><option value="Staff">Staff</option><option value="Alumni">Alumni</option></select></div><div class="ea-table-wrap"><table class="ea-table"><thead><tr><th>Candidate</th><th>Office</th><th>Category</th><th>Votes</th><th>Voter type</th><th>Action</th></tr></thead><tbody data-election-list>@forelse($records as $row)<tr data-row data-search="{{ strtolower(($row->name ?? '').' '.($row->candidate ?? '').' '.($row->position ?? '').' '.($row->category ?? '').' '.($row->acc_type ?? '')) }}" data-category="{{ $row->category ?? '' }}" data-acctype="{{ $row->acc_type ?? '' }}" data-office="{{ strtolower($row->position ?? '') }}" data-entity="{{ $entityField ? strtolower((string) data_get($row, $entityField, '')) : '' }}"><td><div class="ea-person">@if(!empty($row->picture) && file_exists(public_path('storage/picture/'.$row->picture)))<img class="ea-avatar" src="{{ asset('storage/picture/'.$row->picture) }}" alt="">@else<span class="ea-avatar"><i class="fas fa-user"></i></span>@endif<div><strong>{{ $row->name ?? $row->candidate }}</strong><small>{{ $row->candidate }}</small></div></div></td><td>{{ $row->position }}</td><td>{{ $row->category }}</td><td><span class="ea-badge green">{{ number_format((int) ($row->vote ?? 0)) }}</span></td><td>{{ $row->acc_type ?? 'Student' }}</td><td>@if($isCandidates)<button type="button" class="ea-btn danger sm" data-election-delete data-id="{{ $row->id }}"><i class="fas fa-trash"></i></button>@else<span class="ea-badge">Live</span>@endif</td></tr>@empty<tr><td colspan="6"><div class="ea-empty"><i class="fas fa-users"></i>{{ $isCandidates ? 'No candidates found.' : 'No live results available.' }}</div></td></tr>@endforelse</tbody></table></div>
            </div>
        </section>
    @endif
</div>

<div class="ea-drawer" data-election-drawer aria-hidden="true"><div class="ea-dialog"><div class="ea-dialog-head"><div class="ea-dialog-title-wrap"><span class="ea-dialog-icon"><i class="fas fa-sliders"></i></span><div><h3 data-election-dialog-title>Add election record</h3><p data-election-dialog-hint>Provide the details below. The record will be saved without leaving this page.</p></div></div><button type="button" class="ea-close" data-election-close aria-label="Close dialog"><i class="fas fa-times"></i></button></div><form class="ea-form" method="POST" data-election-form>@csrf<input type="hidden" name="id" data-election-id><div data-election-fields></div><div class="ea-form-actions"><button type="button" class="ea-btn light" data-election-close>Cancel</button><button type="submit" class="ea-btn"><span data-election-spinner class="ug-election-spinner"></span><span data-election-submit-label>Save record</span></button></div></form></div></div>
<div class="ea-toast" data-election-toast></div>

<script>
window.initElectionAdmin = window.initElectionAdmin || (() => {
    const root = document.querySelector('[data-election-admin]');
    if (!root) return;
    const drawer = document.querySelector('[data-election-drawer]');
    const form = drawer?.querySelector('[data-election-form]');
    const fields = drawer?.querySelector('[data-election-fields]');
    const toast = document.querySelector('[data-election-toast]');
    const page = root.dataset.electionPage || @json($adminPage);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
    const endpoint = action => `${window.location.origin}/${action} ${page}`;
    const notify = (message, error = false) => { if (!toast) return; toast.textContent = message; toast.classList.toggle('error', error); toast.classList.add('show'); window.setTimeout(() => toast.classList.remove('show'), 3600); };
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
    const close = () => { drawer?.classList.remove('open'); drawer?.setAttribute('aria-hidden', 'true'); };
    document.querySelectorAll('[data-election-tab]').forEach(tab => tab.addEventListener('click', async event => {
        event.preventDefault();
        if (tab.classList.contains('active')) return;
        const shell = document.querySelector('[data-election-admin]');
        shell?.classList.add('is-loading');
        try {
            const response = await fetch(tab.href, {headers:{'Accept':'text/html','X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error();
            const html = await response.text();
            const documentView = new DOMParser().parseFromString(html, 'text/html');
            const nextRoot = documentView.querySelector('[data-election-admin]');
            const nextDrawer = documentView.querySelector('[data-election-drawer]');
            const nextToast = documentView.querySelector('[data-election-toast]');
            if (!nextRoot || !shell) throw new Error();
            shell.replaceWith(nextRoot);
            document.querySelector('[data-election-drawer]')?.replaceWith(nextDrawer);
            document.querySelector('[data-election-toast]')?.replaceWith(nextToast);
            window.history.pushState({}, '', tab.href);
            window.initElectionAdmin();
        } catch (error) { notify('This section could not be loaded. Please try again.', true); shell?.classList.remove('is-loading'); }
    }));
    const input = (name, label, value = '', type = 'text') => `<div class="ea-field"><label>${label}</label><input type="${type}" name="${name}" value="${String(value ?? '').replace(/"/g, '&quot;')}" required></div>`;
    const select = (name, label, value, options) => `<div class="ea-field"><label>${label}</label><select name="${name}" required>${options.map(option => `<option value="${option}" ${String(option) === String(value ?? '') ? 'selected' : ''}>${option}</option>`).join('')}</select></div>`;
    const open = (mode, values = {}) => {
        if (!form || !fields) return;
        form.action = endpoint(mode === 'edit' ? 'update' : 'create');
        form.querySelector('[data-election-id]').value = values.id || '';
        drawer.querySelector('[data-election-dialog-title]').textContent = mode === 'edit' ? 'Edit election record' : 'Add election record';
        const hint = drawer.querySelector('[data-election-dialog-hint]');
        if (hint) hint.textContent = page === 'election settings' ? 'Control an election-wide setting such as the voting switch.' : page === 'election positions' ? 'Add a distinct office and decide where it appears on the ballot.' : 'Add a verified candidate ID and assign the office they will contest.';
        drawer.querySelector('[data-election-submit-label]').textContent = mode === 'edit' ? 'Save changes' : 'Create record';
        if (page === 'election settings') fields.innerHTML = input('description','Setting name',values.description) + input('value','Value',values.value);
        else if (page === 'election positions') fields.innerHTML = input('position','Office name',values.position) + select('category','Category',values.category,['General','Faculty Rep','Hostel Rep','Lga Rep']) + input('order','Display order',values.order,'number') + select('gender','Eligibility',values.gender,['Both','M','F']);
        else fields.innerHTML = input('candidate','Candidate ID number',values.candidate) + `<input type="hidden" name="category" value="{{ $category ?? '' }}"><div class="ea-field"><label>Office</label><select name="position" required><option value="">Select an office</option>@foreach($positions as $pos)<option value="{{ $pos->id }}">{{ $pos->position }} ({{ $pos->category }})</option>@endforeach</select></div>`;
        Object.entries(values).forEach(([key, value]) => { const control = fields.querySelector(`[name="${key}"]`); if (control) control.value = value ?? ''; });
        drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); fields.querySelector('input,select')?.focus();
    };
    document.querySelectorAll('[data-election-create]').forEach(button => button.addEventListener('click', () => open('create')));
    document.querySelectorAll('[data-election-edit]').forEach(button => { button.dataset.bound = '1'; button.addEventListener('click', () => open('edit', {...button.dataset})); });
    document.querySelectorAll('[data-election-close]').forEach(button => button.addEventListener('click', close));
    drawer?.addEventListener('click', event => { if (event.target === drawer) close(); });
    const applyRows = () => {
        const term = root.querySelector('[data-election-search]')?.value.trim().toLowerCase() || '';
        const type = root.querySelector('[data-election-filter="acc_type"]')?.value.trim().toLowerCase() || '';
        const office = root.querySelector('[data-office-filter].active')?.dataset.officeFilter?.toLowerCase() || '';
        const entity = root.querySelector('[data-entity-filter].active')?.dataset.entityFilter?.toLowerCase() || '';
        let visible = 0;
        root.querySelectorAll('[data-row]').forEach(row => { const matches = type !== 'hide' && (!type || String(row.dataset.acctype || '').toLowerCase() === type) && (!office || String(row.dataset.office || '').toLowerCase() === office) && (!entity || String(row.dataset.entity || '').toLowerCase() === entity) && (!term || String(row.dataset.search || '').includes(term)); row.dataset.hidden = matches ? '0' : '1'; if (matches) visible++; });
        const title = root.querySelector('[data-office-title]'); if (title) title.textContent = office ? (root.querySelector(`[data-office-filter="${CSS.escape(office)}"]`)?.textContent.replace(/^\s*\d+\s*/, '').trim() || office) : 'All offices';
        const count = root.querySelector('[data-office-count]'); if (count) count.textContent = `${visible} visible record${visible === 1 ? '' : 's'}`;
    };
    document.querySelectorAll('[data-election-search]').forEach(search => search.addEventListener('input', applyRows));
    document.querySelectorAll('[data-election-filter]').forEach(filter => filter.addEventListener('change', applyRows));
    document.querySelectorAll('[data-office-filter]').forEach(tab => tab.addEventListener('click', () => { document.querySelectorAll('[data-office-filter]').forEach(item => item.classList.toggle('active', item === tab)); applyRows(); }));
    document.querySelectorAll('[data-entity-filter]').forEach(tab => tab.addEventListener('click', () => { document.querySelectorAll('[data-entity-filter]').forEach(item => item.classList.toggle('active', item === tab)); applyRows(); }));
    if (document.querySelector('[data-election-filter="acc_type"]')) applyRows();
    form?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        const payload = new FormData(form);
        const editingId = form.querySelector('[data-election-id]')?.value || '';
        button.disabled = true;
        button.classList.add('is-loading');
        try {
            const response = await fetch(form.action, {method:'POST', body:payload, headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf}});
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'The record could not be saved. Please review the highlighted details.');
            const values = Object.fromEntries(payload.entries());
            const row = editingId ? root.querySelector(`[data-row] [data-election-edit][data-id="${CSS.escape(editingId)}"]`)?.closest('[data-row]') : null;
            if (row) {
                const cells = row.querySelectorAll('td');
                if (page === 'election settings') { if (cells[0]) cells[0].querySelector('strong').textContent = values.description || ''; if (cells[1]) cells[1].textContent = values.value || ''; row.dataset.search = `${values.description || ''} ${values.value || ''}`.toLowerCase(); }
                else if (page === 'election positions') { if (cells[1]) cells[1].querySelector('strong').textContent = values.position || ''; if (cells[2]) cells[2].textContent = values.category || ''; if (cells[3]) cells[3].textContent = values.gender || ''; row.dataset.search = `${values.position || ''} ${values.category || ''} ${values.gender || ''}`.toLowerCase(); row.dataset.category = values.category || ''; }
                const edit = row.querySelector('[data-election-edit]'); if (edit) Object.entries(values).forEach(([key, value]) => edit.dataset[key] = value);
            }
            if (!editingId) {
                const list = root.querySelector('tbody[data-election-list]');
                const id = result.id || `new-${Date.now()}`;
                if (list && page === 'election settings') {
                    const tr = document.createElement('tr'); tr.dataset.row = ''; tr.dataset.search = `${values.description || ''} ${values.value || ''}`.toLowerCase();
                    tr.innerHTML = `<td><strong>${escapeHtml(values.description)}</strong></td><td><span class="ea-badge">${escapeHtml(values.value)}</span></td><td><button type="button" class="ea-btn light sm" data-election-edit data-id="${escapeHtml(id)}" data-description="${escapeHtml(values.description)}" data-value="${escapeHtml(values.value)}"><i class="fas fa-pen"></i> Edit</button> <button type="button" class="ea-btn danger sm" data-election-delete data-id="${escapeHtml(id)}"><i class="fas fa-trash"></i></button></td>`;
                    list.prepend(tr);
                } else if (list && page === 'election positions') {
                    const tr = document.createElement('tr'); tr.dataset.row = ''; tr.dataset.category = values.category || ''; tr.dataset.search = `${values.position || ''} ${values.category || ''} ${values.gender || ''}`.toLowerCase();
                    tr.innerHTML = `<td><span class="ea-badge">${escapeHtml(values.order)}</span></td><td><strong>${escapeHtml(values.position)}</strong></td><td>${escapeHtml(values.category)}</td><td>${escapeHtml(values.gender)}</td><td><button type="button" class="ea-btn light sm" data-election-edit data-id="${escapeHtml(id)}" data-position="${escapeHtml(values.position)}" data-category="${escapeHtml(values.category)}" data-order="${escapeHtml(values.order)}" data-gender="${escapeHtml(values.gender)}"><i class="fas fa-pen"></i> Edit</button> <button type="button" class="ea-btn danger sm" data-election-delete data-id="${escapeHtml(id)}"><i class="fas fa-trash"></i></button></td>`;
                    list.prepend(tr);
                } else if (list && ['election candidates','election general','election faculty','election hostel','election lga'].includes(page)) {
                    const office = fields.querySelector('select[name="position"]')?.selectedOptions?.[0]?.textContent || values.position || 'Office';
                    const categoryName = page === 'election candidates' ? 'Candidate' : page.replace('election ', '');
                    const tr = document.createElement('tr'); tr.dataset.row = ''; tr.dataset.category = categoryName; tr.dataset.acctype = 'student'; tr.dataset.office = office.toLowerCase(); tr.dataset.entity = String(result.entity || '').toLowerCase(); tr.dataset.search = `${values.candidate || ''} ${office} ${categoryName} ${result.entity || ''}`.toLowerCase();
                    tr.innerHTML = `<td><div class="ea-person"><span class="ea-avatar"><i class="fas fa-user"></i></span><div><strong>${escapeHtml(values.candidate)}</strong><small>${escapeHtml(values.candidate)}</small></div></div></td><td>${escapeHtml(office)}</td><td>${escapeHtml(categoryName)}</td><td><span class="ea-badge green">0</span></td><td>Student</td><td><button type="button" class="ea-btn light sm" data-election-edit data-id="${escapeHtml(id)}" data-candidate="${escapeHtml(values.candidate)}" data-position="${escapeHtml(values.position)}"><i class="fas fa-pen"></i> Edit</button> <button type="button" class="ea-btn danger sm" data-election-delete data-id="${escapeHtml(id)}"><i class="fas fa-trash"></i></button></td>`;
                    list.prepend(tr);
                }
            }
            notify(editingId ? 'Changes saved.' : 'Record created.'); close();
        } catch (error) { notify(error.message || 'The record could not be saved. Please try again.', true); }
        finally { button.disabled = false; button.classList.remove('is-loading'); }
    });
    const removeRow = async button => { if (!window.confirm('Remove this election record?')) return; const data = new FormData(); data.append('_token', csrf); data.append('id', button.dataset.id); button.disabled = true; try { const response = await fetch(endpoint('delete'), {method:'POST', body:data, headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf}}); if (!response.ok) throw new Error(); button.closest('[data-row]')?.remove(); notify('Record removed.'); } catch (error) { button.disabled = false; notify('The record could not be removed.', true); } };
    document.querySelectorAll('[data-election-delete]').forEach(button => { button.dataset.bound = '1'; button.addEventListener('click', () => removeRow(button)); });
    root.addEventListener('click', event => { const edit = event.target.closest('[data-election-edit]'); if (edit && !edit.dataset.bound) { edit.dataset.bound = '1'; open('edit', {...edit.dataset}); return; } const remove = event.target.closest('[data-election-delete]'); if (remove && !remove.dataset.bound) { remove.dataset.bound = '1'; removeRow(remove); } });
});
window.initElectionAdmin();
</script>
