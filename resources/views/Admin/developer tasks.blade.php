<style>
    .dt-page{--dt-ink:#17324d;--dt-muted:#6e8294;--dt-line:#e2ebf2;--dt-blue:#1769aa;background:#f4f8fb;min-height:calc(100vh - 90px);padding:24px}
    .dt-hero{border-radius:22px;padding:25px 28px;background:linear-gradient(135deg,#123d60,#1c77a7);color:#fff;box-shadow:0 15px 30px rgba(20,65,98,.18)}
    .dt-hero h1{font-size:1.65rem;margin:0 0 7px;font-weight:800;color:#fff!important}.dt-hero .small{color:#e7f4fb!important}.dt-hero p{margin:0;color:#dcecf6!important;max-width:720px;line-height:1.6}
    .dt-hero-actions{display:flex;gap:9px;justify-content:flex-end;align-items:center}.dt-hero .btn{border-radius:11px;font-weight:700}.dt-hero .btn-light{color:#16547b}
    .dt-stat{height:100%;border:1px solid var(--dt-line);border-radius:17px;background:#fff;padding:17px 18px;box-shadow:0 7px 20px rgba(34,70,96,.05)}.dt-stat small{display:block;color:var(--dt-muted);font-weight:700;font-size:.72rem}.dt-stat strong{display:block;color:var(--dt-ink);font-size:1.55rem;margin-top:4px}.dt-stat i{float:right;margin-top:2px;font-size:1.2rem;color:#4b99c3}
    .dt-panel{border:1px solid var(--dt-line);border-radius:18px;background:#fff;box-shadow:0 7px 20px rgba(34,70,96,.05)}.dt-panel-head{padding:18px 20px;border-bottom:1px solid var(--dt-line);display:flex;justify-content:space-between;align-items:center;gap:12px}.dt-panel-head h2{margin:0;color:var(--dt-ink);font-size:1.05rem;font-weight:800}.dt-panel-body{padding:18px 20px}
    .dt-table{width:100%;border-collapse:separate;border-spacing:0}.dt-table th{padding:12px 13px;background:#f5f8fb;color:#668095;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}.dt-table td{padding:14px 13px;border-top:1px solid #edf2f6;color:#29445b;vertical-align:middle}.dt-table tr:hover td{background:#fbfdff}.dt-title{font-weight:800;color:#184d78;text-decoration:none}.dt-title:hover{color:#0d78bb}.dt-description{display:block;max-width:380px;color:#8294a3;font-size:.77rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:4px}.dt-badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 9px;font-size:.67rem;font-weight:800;white-space:nowrap}.dt-badge.assigned{color:#805b0c;background:#fff4d9}.dt-badge.in_progress{color:#15689a;background:#e6f4fb}.dt-badge.review{color:#6846a0;background:#f0eaff}.dt-badge.changes_requested{color:#a23c35;background:#ffebe8}.dt-badge.confirmed{color:#16754a;background:#e5f7ee}.dt-badge.low{color:#4b6578;background:#eef4f7}.dt-badge.medium{color:#246a9b;background:#e7f2fa}.dt-badge.high{color:#93620b;background:#fff2d9}.dt-badge.urgent{color:#a52d31;background:#ffe7e7}.dt-empty{text-align:center;padding:48px 20px;color:var(--dt-muted)}.dt-empty i{font-size:2rem;color:#9ab2c2;margin-bottom:10px}.dt-filter{border:1px solid var(--dt-line);border-radius:12px;padding:9px 11px;font-size:.82rem;color:#26465d;background:#fff}.dt-filter:focus{border-color:#78b2d0;box-shadow:0 0 0 .18rem rgba(35,130,174,.12)}.dt-mobile-actions{display:none}
    @media(max-width:900px){.dt-page{padding:14px}.dt-hero{padding:21px}.dt-hero-actions{justify-content:flex-start;margin-top:15px}.dt-table-wrap{overflow-x:auto}.dt-table{min-width:860px}}
    @media(max-width:600px){.dt-page{padding:10px}.dt-hero h1{font-size:1.35rem}.dt-panel-head,.dt-panel-body{padding:14px}.dt-stat{padding:13px}.dt-stat strong{font-size:1.3rem}.dt-mobile-actions{display:flex;justify-content:flex-end;margin-top:6px}.dt-desktop-actions{display:none}}
</style>
<div class="dt-page">
    <div class="dt-hero mb-3">
        <div class="row align-items-center">
            <div class="col-lg-8"><div class="small text-uppercase fw-bold opacity-75 mb-2"><i class="fas fa-comments me-1"></i> Communication · Delivery workspace</div><h1>Developer task board</h1><p>Assign work, share the full brief, follow progress, and close each task with a clear confirmation trail.</p></div>
            <div class="col-lg-4 dt-hero-actions">@if($isTaskAdmin)<a href="{{ route('developer.tasks.create') }}" class="btn btn-light"><i class="fas fa-plus me-1"></i> Assign a task</a>@endif</div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3"><div class="dt-stat"><i class="fas fa-layer-group"></i><small>Total tasks</small><strong>{{ number_format($taskStats['total']) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="dt-stat"><i class="fas fa-bolt"></i><small>Open work</small><strong>{{ number_format($taskStats['open']) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="dt-stat"><i class="fas fa-eye"></i><small>Waiting for review</small><strong>{{ number_format($taskStats['review']) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="dt-stat"><i class="fas fa-circle-check"></i><small>Confirmed</small><strong>{{ number_format($taskStats['confirmed']) }}</strong></div></div>
    </div>
    <div class="dt-panel">
        <div class="dt-panel-head"><div><h2><i class="fas fa-list-check me-2 text-primary"></i>Tasks</h2><small class="text-muted">Every assignment and decision stays in one audit-friendly timeline.</small></div></div>
        <div class="dt-panel-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-lg-5"><input class="form-control dt-filter" name="q" value="{{ $taskFilters['q'] ?? '' }}" placeholder="Search task title, brief or assignee"></div>
                <div class="col-6 col-lg-2"><select class="form-select dt-filter" name="status"><option value="">All statuses</option>@foreach(['assigned'=>'Assigned','in_progress'=>'In progress','review'=>'Ready for review','changes_requested'=>'Changes requested','confirmed'=>'Confirmed'] as $value=>$label)<option value="{{ $value }}" {{ ($taskFilters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-2"><select class="form-select dt-filter" name="priority"><option value="">All priorities</option>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $value=>$label)<option value="{{ $value }}" {{ ($taskFilters['priority'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                <div class="col-lg-3 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i>Filter</button><a href="{{ route('developer.tasks.index') }}" class="btn btn-light border">Clear</a></div>
            </form>
            @if($taskData->count())
                <div class="dt-table-wrap"><table class="dt-table"><thead><tr><th>Task</th><th>Priority</th><th>Status</th><th>Assigned to</th><th>Due</th><th>Last updated</th><th></th></tr></thead><tbody>
                    @foreach($taskData as $task)
                        <tr><td><a class="dt-title" href="{{ route('developer.tasks.show',$task->id) }}">{{ $task->title }}</a><span class="dt-description">{{ trim(strip_tags($task->description)) }}</span><div class="dt-mobile-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('developer.tasks.show',$task->id) }}">Open</a></div></td><td><span class="dt-badge {{ $task->priority }}">{{ ucfirst($task->priority) }}</span></td><td><span class="dt-badge {{ $task->status }}">{{ ['assigned'=>'Assigned','in_progress'=>'In progress','review'=>'Ready for review','changes_requested'=>'Changes requested','confirmed'=>'Confirmed'][$task->status] ?? ucfirst(str_replace('_',' ',$task->status)) }}</span></td><td>{{ $task->assignee_name ?: ($task->assigned_to ?: 'Unassigned') }}</td><td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No deadline' }}</td><td>{{ $task->updated_at ? \Carbon\Carbon::parse($task->updated_at)->diffForHumans() : '—' }}</td><td class="text-end dt-desktop-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('developer.tasks.show',$task->id) }}"><i class="fas fa-arrow-up-right-from-square me-1"></i>Open</a></td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><small class="text-muted">Showing {{ $taskData->firstItem() }}–{{ $taskData->lastItem() }} of {{ $taskData->total() }}</small>{{ $taskData->links() }}</div>
            @else
                <div class="dt-empty"><i class="fas fa-clipboard-check d-block"></i><h5 class="mb-1">No tasks found</h5><p class="mb-0">New assignments will appear here with their priority, owner and current status.</p></div>
            @endif
        </div>
    </div>
</div>
