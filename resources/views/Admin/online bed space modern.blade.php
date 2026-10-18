@php
    $filters = $filters ?? [];
    $bedTypes = $bedTypes ?? [0 => 'Online allocation', 1 => 'Reserved allocation', 2 => 'New student', 3 => 'Remedial / SBRS'];
    $stats = $stats ?? ['total' => count($data ?? []), 'available' => 0, 'occupied' => 0];
    $rowStart = method_exists($data, 'firstItem') ? ($data->firstItem() ?? 1) : 1;
@endphp

<style>
    .ug-online-hostel { --ug-blue:#168acb; --ug-ink:#18324b; --ug-muted:#6b7d8e; }
    .ug-online-hostel .ug-hero { background:linear-gradient(120deg,#184f78,#168acb); border-radius:18px; color:#fff; box-shadow:0 12px 28px rgba(19,82,125,.18); }
    .ug-online-hostel .ug-hero h3 { color:#fff; font-weight:700; margin-bottom:.35rem; }
    .ug-online-hostel .ug-hero p { color:rgba(255,255,255,.84); margin:0; }
    .ug-online-hostel .ug-hero-icon { width:58px; height:58px; border-radius:16px; display:grid; place-items:center; background:rgba(255,255,255,.17); font-size:1.55rem; }
    .ug-online-hostel .ug-stat { border:1px solid #e5edf3; border-radius:14px; padding:1rem; height:100%; background:#fff; box-shadow:0 5px 18px rgba(32,64,88,.05); }
    .ug-online-hostel .ug-stat small { display:block; color:var(--ug-muted); margin-top:.25rem; }
    .ug-online-hostel .ug-stat strong { color:var(--ug-ink); font-size:1.45rem; line-height:1; }
    .ug-online-hostel .ug-stat-icon { width:38px; height:38px; border-radius:11px; display:grid; place-items:center; margin-bottom:.65rem; }
    .ug-online-hostel .ug-stat-icon.blue { color:#0878b7; background:#e4f4fc; }
    .ug-online-hostel .ug-stat-icon.green { color:#168457; background:#e5f7ef; }
    .ug-online-hostel .ug-stat-icon.amber { color:#a76c00; background:#fff4d9; }
    .ug-online-hostel .ug-filter, .ug-online-hostel .ug-table-card { border:1px solid #e2ebf1; border-radius:16px; background:#fff; box-shadow:0 5px 18px rgba(32,64,88,.04); }
    .ug-online-hostel .ug-filter .form-label { color:#476174; font-size:.76rem; text-transform:uppercase; letter-spacing:.045em; font-weight:700; }
    .ug-online-hostel .form-control, .ug-online-hostel .form-select { min-height:42px; border-color:#d9e5ec; border-radius:10px; }
    .ug-online-hostel .form-control:focus, .ug-online-hostel .form-select:focus { border-color:var(--ug-blue); box-shadow:0 0 0 .2rem rgba(22,138,203,.12); }
    .ug-online-hostel .ug-table-card { overflow:hidden; }
    .ug-online-hostel .ug-table-card .table { margin-bottom:0; }
    .ug-online-hostel .ug-table-card thead th { background:#f5f9fc; color:#536c7d; border-bottom:1px solid #e1ebf1; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .ug-online-hostel .ug-table-card tbody td { color:#294459; vertical-align:middle; white-space:nowrap; }
    .ug-online-hostel .ug-table-card tbody tr:hover { background:#f8fcfe; }
    .ug-online-hostel .ug-location { color:var(--ug-ink); font-weight:600; }
    .ug-online-hostel .ug-location small { display:block; color:var(--ug-muted); font-weight:400; }
    .ug-online-hostel .ug-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.3rem .6rem; font-size:.74rem; font-weight:700; }
    .ug-online-hostel .ug-badge.available { color:#177b51; background:#e4f7ed; }
    .ug-online-hostel .ug-badge.occupied { color:#9a5f00; background:#fff3d8; }
    .ug-online-hostel .ug-badge.type { color:#176a94; background:#e7f4fb; }
    .ug-online-hostel .ug-empty { padding:3rem 1rem; text-align:center; color:var(--ug-muted); }
    .ug-online-hostel .ug-empty i { color:#9ab5c5; font-size:2rem; margin-bottom:.65rem; }
    .ug-online-hostel .ug-pagination { border-top:1px solid #e7eef3; padding:.85rem 1rem; }
    .ug-online-hostel .ug-pagination .pagination { margin:0; flex-wrap:wrap; gap:.25rem; }
    .ug-online-hostel .ug-pagination .page-link { border:1px solid #dce7ee; border-radius:8px !important; color:#176a94; padding:.38rem .68rem; }
    .ug-online-hostel .ug-pagination .page-item.active .page-link { background:#168acb; border-color:#168acb; color:#fff; }
    .ug-online-hostel .ug-pagination .page-item.disabled .page-link { color:#aab8c1; background:#f7fafc; }
    @media (max-width:767.98px) { .ug-online-hostel .ug-hero { border-radius:14px; } .ug-online-hostel .ug-hero-icon { width:48px; height:48px; } .ug-online-hostel .ug-stat strong { font-size:1.2rem; } }
</style>

<div class="main-body ug-online-hostel"><div class="page-wrapper"><div class="row g-3">
    <div class="col-12"><section class="ug-hero p-4"><div class="d-flex align-items-center justify-content-between gap-3"><div><h3><i class="fas fa-wifi me-2"></i>Online bed spaces</h3><p>Review spaces prepared for online hostel allocation and monitor their current occupancy.</p></div><div class="ug-hero-icon d-none d-sm-grid"><i class="fas fa-bed"></i></div></div></section></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon blue"><i class="fas fa-layer-group"></i></span><strong>{{ number_format($stats['total']) }}</strong><small>Spaces in this view</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon green"><i class="fas fa-circle-check"></i></span><strong>{{ number_format($stats['available']) }}</strong><small>Available spaces</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon amber"><i class="fas fa-user-check"></i></span><strong>{{ number_format($stats['occupied']) }}</strong><small>Occupied spaces</small></div></div>
    <div class="col-12"><div class="ug-filter p-3 p-md-4"><div class="d-flex align-items-center justify-content-between mb-3 gap-2"><div><h5 class="mb-1" style="color:#18324b">Find online spaces</h5><small class="text-muted">Online allocation spaces are shown by default. You can also review other assignment types.</small></div><a href="{{ url('online bed space') }}" class="btn btn-light btn-sm border"><i class="fas fa-rotate-left me-1"></i>Reset</a></div>
        <form method="GET" action="{{ url('online bed space') }}"><div class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-3"><label class="form-label" for="ug-online-search">Search location</label><input id="ug-online-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Hall, block, room, bed or occupant"></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-online-hall">Hall</label><select id="ug-online-hall" name="hall" class="form-select"><option value="all">All halls</option>@foreach ($hall as $item)<option value="{{ $item->hall }}" {{ (($filters['hall'] ?? '') === $item->hall) ? 'selected' : '' }}>{{ $item->hall }}</option>@endforeach</select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-online-category">Category</label><select id="ug-online-category" name="category" class="form-select"><option value="all">All categories</option>@foreach (($categories ?? []) as $item)<option value="{{ $item->category }}" {{ (($filters['category'] ?? '') === $item->category) ? 'selected' : '' }}>{{ ucwords(strtolower($item->category)) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-online-type">Assignment type</label><select id="ug-online-type" name="bed_type" class="form-select"><option value="all" {{ (($filters['bed_type'] ?? '') === 'all') ? 'selected' : '' }}>All types</option>@foreach ($bedTypes as $value => $label)<option value="{{ $value }}" {{ ((string)($filters['bed_type'] ?? '') === (string)$value || (!array_key_exists('bed_type', $filters) && (int)$value === 0)) ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-online-status">View</label><select id="ug-online-status" name="status" class="form-select"><option value="all" {{ (($filters['status'] ?? '') === 'all' || !array_key_exists('status', $filters)) ? 'selected' : '' }}>Available and occupied</option><option value="0" {{ (($filters['status'] ?? '') === '0') ? 'selected' : '' }}>Available only</option><option value="1" {{ (($filters['status'] ?? '') === '1') ? 'selected' : '' }}>Occupied only</option></select></div>
            <div class="col-12 col-md-6 col-lg-1"><button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i>Find</button></div>
        </div></form>
    </div></div>
    <div class="col-12"><div class="ug-table-card"><div class="p-3 d-flex align-items-center justify-content-between gap-2 flex-wrap"><div><h5 class="mb-1" style="color:#18324b">Online space list</h5><small class="text-muted">Use the location and assignment type to identify spaces quickly.</small></div><span class="badge bg-light text-dark border">{{ number_format($stats['total']) }} record(s)</span></div><div class="table-responsive">
        <table id="ug-online-bed-table" class="table table-hover align-middle" style="width:100%"><thead><tr><th>#</th><th>Location</th><th>Category</th><th>Assignment type</th><th>Status</th><th>Occupant</th></tr></thead><tbody>
        @forelse ($data as $row)<tr><td>{{ $rowStart + $loop->index }}</td><td class="ug-location">{{ $row->hall }}<small>{{ $row->block }} · Room {{ $row->room }} · Bed {{ $row->bed }}</small></td><td>{{ $row->category ? ucwords(strtolower($row->category)) : '—' }}</td><td><span class="ug-badge type"><i class="fas fa-tag"></i>{{ $bedTypes[(int)$row->bed_type] ?? 'Other' }}</span></td><td>@if ((int)$row->status === 0)<span class="ug-badge available"><i class="fas fa-circle-check"></i>Available</span>@else<span class="ug-badge occupied"><i class="fas fa-user-check"></i>Occupied</span>@endif</td><td>{{ $row->occupant ?: '—' }}</td></tr>@empty<tr><td colspan="6"><div class="ug-empty"><i class="fas fa-bed d-block"></i><strong>No online bed spaces match these filters.</strong><div>Try clearing a filter or selecting “All types”.</div></div></td></tr>@endforelse
        </tbody></table>
    </div><div class="ug-pagination d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"><small class="text-muted">Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() ?? 0 }} spaces</small><div>{{ $data->onEachSide(1)->links('pagination::bootstrap-4') }}</div></div></div></div>
</div></div></div>
