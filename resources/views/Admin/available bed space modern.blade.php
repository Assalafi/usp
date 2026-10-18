@php
    $filters = $filters ?? [];
    $bedTypes = $bedTypes ?? [0 => 'Online allocation', 1 => 'Reserved allocation', 2 => 'New student', 3 => 'Remedial / SBRS'];
    $stats = $stats ?? ['total' => count($data ?? []), 'available' => 0, 'occupied' => 0];
@endphp

<style>
    .ug-hostel-admin { --ug-blue: #168acb; --ug-ink: #18324b; --ug-muted: #6b7d8e; }
    .ug-hostel-admin .ug-hero { background: linear-gradient(120deg, #0e6fa8, #20a6dc); border: 0; border-radius: 18px; color: #fff; box-shadow: 0 12px 28px rgba(19, 104, 155, .18); }
    .ug-hostel-admin .ug-hero h3 { color: #fff; font-weight: 700; margin-bottom: .35rem; }
    .ug-hostel-admin .ug-hero p { color: rgba(255,255,255,.84); margin: 0; }
    .ug-hostel-admin .ug-hero-icon { width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center; background: rgba(255,255,255,.17); font-size: 1.55rem; }
    .ug-hostel-admin .ug-stat { border: 1px solid #e5edf3; border-radius: 14px; padding: 1rem; height: 100%; background: #fff; box-shadow: 0 5px 18px rgba(32, 64, 88, .05); }
    .ug-hostel-admin .ug-stat small { display: block; color: var(--ug-muted); margin-top: .25rem; }
    .ug-hostel-admin .ug-stat strong { color: var(--ug-ink); font-size: 1.45rem; line-height: 1; }
    .ug-hostel-admin .ug-stat-icon { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; margin-bottom: .65rem; }
    .ug-hostel-admin .ug-stat-icon.blue { color: #0878b7; background: #e4f4fc; }
    .ug-hostel-admin .ug-stat-icon.green { color: #168457; background: #e5f7ef; }
    .ug-hostel-admin .ug-stat-icon.amber { color: #a76c00; background: #fff4d9; }
    .ug-hostel-admin .ug-filter, .ug-hostel-admin .ug-table-card { border: 1px solid #e2ebf1; border-radius: 16px; background: #fff; box-shadow: 0 5px 18px rgba(32, 64, 88, .04); }
    .ug-hostel-admin .ug-filter .form-label { color: #476174; font-size: .76rem; text-transform: uppercase; letter-spacing: .045em; font-weight: 700; }
    .ug-hostel-admin .form-control, .ug-hostel-admin .form-select { min-height: 42px; border-color: #d9e5ec; border-radius: 10px; }
    .ug-hostel-admin .form-control:focus, .ug-hostel-admin .form-select:focus { border-color: var(--ug-blue); box-shadow: 0 0 0 .2rem rgba(22,138,203,.12); }
    .ug-hostel-admin .ug-table-card { overflow: hidden; }
    .ug-hostel-admin .ug-table-card .table { margin-bottom: 0; }
    .ug-hostel-admin .ug-table-card thead th { background: #f5f9fc; color: #536c7d; border-bottom: 1px solid #e1ebf1; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .ug-hostel-admin .ug-table-card tbody td { color: #294459; vertical-align: middle; white-space: nowrap; }
    .ug-hostel-admin .ug-table-card tbody tr:hover { background: #f8fcfe; }
    .ug-hostel-admin .ug-location { font-weight: 600; color: var(--ug-ink); }
    .ug-hostel-admin .ug-location small { display: block; color: var(--ug-muted); font-weight: 400; }
    .ug-hostel-admin .ug-badge { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; padding: .3rem .6rem; font-size: .74rem; font-weight: 700; }
    .ug-hostel-admin .ug-badge.available { color: #177b51; background: #e4f7ed; }
    .ug-hostel-admin .ug-badge.occupied { color: #9a5f00; background: #fff3d8; }
    .ug-hostel-admin .ug-badge.type { color: #176a94; background: #e7f4fb; }
    .ug-hostel-admin .ug-empty { padding: 3rem 1rem; text-align: center; color: var(--ug-muted); }
    .ug-hostel-admin .ug-empty i { color: #9ab5c5; font-size: 2rem; margin-bottom: .65rem; }
    .ug-hostel-admin .modal-content { border: 0; border-radius: 16px; box-shadow: 0 18px 45px rgba(20, 49, 73, .22); }
    .ug-hostel-admin .modal-header { background: #f2f8fc; border-bottom: 1px solid #e3edf2; }
    @media (max-width: 767.98px) { .ug-hostel-admin .ug-hero { border-radius: 14px; } .ug-hostel-admin .ug-hero-icon { width: 48px; height: 48px; } .ug-hostel-admin .ug-stat strong { font-size: 1.2rem; } }
</style>

<div class="main-body ug-hostel-admin"><div class="page-wrapper"><div class="row g-3">
    <div class="col-12"><section class="ug-hero p-4"><div class="d-flex align-items-center justify-content-between gap-3"><div><h3><i class="fas fa-bed me-2"></i>Available bed spaces</h3><p>Find an open space by hall, category, and assignment type, then assign it to a student.</p></div><div class="ug-hero-icon d-none d-sm-grid"><i class="fas fa-building"></i></div></div></section></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon blue"><i class="fas fa-layer-group"></i></span><strong>{{ number_format($stats['total']) }}</strong><small>Spaces in this view</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon green"><i class="fas fa-check-circle"></i></span><strong>{{ number_format($stats['available']) }}</strong><small>Available to assign</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon amber"><i class="fas fa-user-check"></i></span><strong>{{ number_format($stats['occupied']) }}</strong><small>Already occupied</small></div></div>
    <div class="col-12"><div class="ug-filter p-3 p-md-4"><div class="d-flex align-items-center justify-content-between mb-3 gap-2"><div><h5 class="mb-1" style="color:#18324b">Find a bed space</h5><small class="text-muted">Use any combination of filters. Available spaces are shown by default.</small></div><a href="{{ url('available bed space') }}" class="btn btn-light btn-sm border"><i class="fas fa-rotate-left me-1"></i>Reset</a></div>
        <form method="GET" action="{{ url('available bed space') }}"><div class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-lg-3"><label class="form-label" for="ug-bed-search">Search location</label><input id="ug-bed-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Hall, block, room or bed"></div>
            <div class="col-12 col-md-4 col-lg-2"><label class="form-label" for="ug-bed-hall">Hall</label><select id="ug-bed-hall" name="hall" class="form-select"><option value="all">All halls</option>@foreach ($hall as $item)<option value="{{ $item->hall }}" @selected(($filters['hall'] ?? '') === $item->hall)>{{ $item->hall }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4 col-lg-2"><label class="form-label" for="ug-bed-category">Category</label><select id="ug-bed-category" name="category" class="form-select"><option value="all">All categories</option>@foreach (($categories ?? []) as $item)<option value="{{ $item->category }}" @selected(($filters['category'] ?? '') === $item->category)>{{ ucwords(strtolower($item->category)) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4 col-lg-2"><label class="form-label" for="ug-bed-type">Assignment type</label><select id="ug-bed-type" name="bed_type" class="form-select"><option value="all">All types</option>@foreach ($bedTypes as $value => $label)<option value="{{ $value }}" @selected((string)($filters['bed_type'] ?? '') === (string)$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4 col-lg-2"><label class="form-label" for="ug-bed-status">View</label><select id="ug-bed-status" name="status" class="form-select"><option value="0" @selected(($filters['status'] ?? '') === '0' || !array_key_exists('status', $filters))>Available only</option><option value="1" @selected(($filters['status'] ?? '') === '1')>Occupied only</option><option value="all" @selected(($filters['status'] ?? '') === 'all')>Available and occupied</option></select></div>
            <div class="col-12 col-md-4 col-lg-1"><button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i>Find</button></div>
        </div></form>
    </div></div>
    <div class="col-12"><div class="ug-table-card"><div class="p-3 d-flex align-items-center justify-content-between gap-2 flex-wrap"><div><h5 class="mb-1" style="color:#18324b">Bed space list</h5><small class="text-muted">Assign only after confirming the student’s ID number.</small></div><span class="badge bg-light text-dark border">{{ number_format($stats['total']) }} record(s)</span></div><div class="table-responsive">
        <table id="export-table" class="display table table-hover align-middle" style="width:100%"><thead><tr><th>#</th><th>Location</th><th>Category</th><th>Assignment type</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
        @forelse ($data as $row)<tr><td>{{ $loop->iteration }}</td><td class="ug-location">{{ $row->hall }}<small>{{ $row->block }} · Room {{ $row->room }} · Bed {{ $row->bed }}</small></td><td>{{ $row->category ? ucwords(strtolower($row->category)) : '—' }}</td><td><span class="ug-badge type"><i class="fas fa-tag"></i>{{ $bedTypes[(int)$row->bed_type] ?? 'Other' }}</span></td><td>@if ((int)$row->status === 0)<span class="ug-badge available"><i class="fas fa-circle-check"></i>Available</span>@else<span class="ug-badge occupied"><i class="fas fa-user-check"></i>Occupied</span>@endif</td><td class="text-end">@if ((int)$row->status === 0)<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignBed{{ $row->id }}"><i class="fas fa-user-plus me-1"></i>Assign</button>@else<span class="text-muted small">{{ $row->occupant ?: 'Occupied' }}</span>@endif</td></tr>@empty<tr><td colspan="6"><div class="ug-empty"><i class="fas fa-bed d-block"></i><strong>No bed spaces match these filters.</strong><div>Try clearing a filter or select “Available and occupied”.</div></div></td></tr>@endforelse
        </tbody></table>
    </div></div></div>
</div></div></div>

@foreach ($data as $row)
    @if ((int)$row->status === 0)
        <div id="assignBed{{ $row->id }}" class="modal fade" tabindex="-1" aria-labelledby="assignBedLabel{{ $row->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title mb-1" id="assignBedLabel{{ $row->id }}">Assign bed space</h5><small class="text-muted">{{ $row->hall }} · {{ $row->block }} · Room {{ $row->room }} · Bed {{ $row->bed }}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form action="{{ url('assign bed') }}" method="POST"><div class="modal-body">@csrf<label class="form-label fw-semibold" for="username{{ $row->id }}">Student ID number</label><input type="text" class="form-control" name="username" id="username{{ $row->id }}" required autocomplete="off" placeholder="e.g. 22/08/08/0010"><small class="text-muted d-block mt-2">The system checks that the student does not already have a bed and that the gender matches the bed.</small><input type="hidden" name="id" value="{{ $row->id }}"></div><div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>Assign bed</button></div></form></div></div></div>
    @endif
@endforeach
