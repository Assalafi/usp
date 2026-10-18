@php
    $filters = $filters ?? [];
    $bedTypes = $bedTypes ?? [0 => 'Online allocation', 1 => 'Reserved allocation', 2 => 'New student', 3 => 'Remedial / SBRS'];
    $stats = $stats ?? ['total' => count($data ?? []), 'paid' => 0, 'unpaid' => 0];
    $rowStart = method_exists($data, 'firstItem') ? ($data->firstItem() ?? 1) : 1;
@endphp

<style>
    .ug-recipient-admin { --ug-blue: #168acb; --ug-ink: #18324b; --ug-muted: #6b7d8e; }
    .ug-recipient-admin .ug-hero { background: linear-gradient(120deg, #184f78, #168acb); border-radius: 18px; color: #fff; box-shadow: 0 12px 28px rgba(19, 82, 125, .18); }
    .ug-recipient-admin .ug-hero h3 { color: #fff; font-weight: 700; margin-bottom: .35rem; }
    .ug-recipient-admin .ug-hero p { color: rgba(255,255,255,.84); margin: 0; }
    .ug-recipient-admin .ug-hero-icon { width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center; background: rgba(255,255,255,.17); font-size: 1.55rem; }
    .ug-recipient-admin .ug-stat { border: 1px solid #e5edf3; border-radius: 14px; padding: 1rem; height: 100%; background: #fff; box-shadow: 0 5px 18px rgba(32,64,88,.05); }
    .ug-recipient-admin .ug-stat small { display: block; color: var(--ug-muted); margin-top: .25rem; }
    .ug-recipient-admin .ug-stat strong { color: var(--ug-ink); font-size: 1.45rem; line-height: 1; }
    .ug-recipient-admin .ug-stat-icon { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; margin-bottom: .65rem; }
    .ug-recipient-admin .ug-stat-icon.blue { color: #0878b7; background: #e4f4fc; }
    .ug-recipient-admin .ug-stat-icon.green { color: #168457; background: #e5f7ef; }
    .ug-recipient-admin .ug-stat-icon.amber { color: #a76c00; background: #fff4d9; }
    .ug-recipient-admin .ug-filter, .ug-recipient-admin .ug-table-card { border: 1px solid #e2ebf1; border-radius: 16px; background: #fff; box-shadow: 0 5px 18px rgba(32,64,88,.04); }
    .ug-recipient-admin .ug-filter .form-label { color: #476174; font-size: .76rem; text-transform: uppercase; letter-spacing: .045em; font-weight: 700; }
    .ug-recipient-admin .form-control, .ug-recipient-admin .form-select { min-height: 42px; border-color: #d9e5ec; border-radius: 10px; }
    .ug-recipient-admin .form-control:focus, .ug-recipient-admin .form-select:focus { border-color: var(--ug-blue); box-shadow: 0 0 0 .2rem rgba(22,138,203,.12); }
    .ug-recipient-admin .ug-table-card { overflow: hidden; }
    .ug-recipient-admin .ug-table-card .table { margin-bottom: 0; }
    .ug-recipient-admin .ug-table-card thead th { background: #f5f9fc; color: #536c7d; border-bottom: 1px solid #e1ebf1; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .ug-recipient-admin .ug-table-card tbody td { color: #294459; vertical-align: middle; white-space: nowrap; }
    .ug-recipient-admin .ug-table-card tbody tr:hover { background: #f8fcfe; }
    .ug-recipient-admin .ug-name { color: var(--ug-ink); font-weight: 600; }
    .ug-recipient-admin .ug-name small { display: block; color: var(--ug-muted); font-weight: 400; }
    .ug-recipient-admin .ug-badge { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; padding: .3rem .6rem; font-size: .74rem; font-weight: 700; }
    .ug-recipient-admin .ug-badge.paid { color: #177b51; background: #e4f7ed; }
    .ug-recipient-admin .ug-badge.pending { color: #9a5f00; background: #fff3d8; }
    .ug-recipient-admin .ug-badge.type { color: #176a94; background: #e7f4fb; }
    .ug-recipient-admin .ug-empty { padding: 3rem 1rem; text-align: center; color: var(--ug-muted); }
    .ug-recipient-admin .ug-empty i { color: #9ab5c5; font-size: 2rem; margin-bottom: .65rem; }
    .ug-recipient-admin .ug-pagination { border-top: 1px solid #e7eef3; padding: .85rem 1rem; }
    .ug-recipient-admin .ug-pagination .pagination { margin: 0; flex-wrap: wrap; gap: .25rem; }
    .ug-recipient-admin .ug-pagination .page-link { border: 1px solid #dce7ee; border-radius: 8px !important; color: #176a94; padding: .38rem .68rem; }
    .ug-recipient-admin .ug-pagination .page-item.active .page-link { background: #168acb; border-color: #168acb; color: #fff; }
    .ug-recipient-admin .ug-pagination .page-item.disabled .page-link { color: #aab8c1; background: #f7fafc; }
    .ug-recipient-admin .modal-content { border: 0; border-radius: 16px; box-shadow: 0 18px 45px rgba(20,49,73,.22); }
    .ug-recipient-admin .modal-header { background: #f2f8fc; border-bottom: 1px solid #e3edf2; }
    @media (max-width: 767.98px) { .ug-recipient-admin .ug-hero { border-radius: 14px; } .ug-recipient-admin .ug-hero-icon { width: 48px; height: 48px; } .ug-recipient-admin .ug-stat strong { font-size: 1.2rem; } }
</style>

<div class="main-body ug-recipient-admin"><div class="page-wrapper"><div class="row g-3">
    <div class="col-12"><section class="ug-hero p-4"><div class="d-flex align-items-center justify-content-between gap-3"><div><h3><i class="fas fa-users me-2"></i>Hostel recipients</h3><p>Review assigned students, confirm hostel payment, and download permits from one place.</p></div><div class="ug-hero-icon d-none d-sm-grid"><i class="fas fa-clipboard-check"></i></div></div></section></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon blue"><i class="fas fa-users"></i></span><strong>{{ number_format($stats['total']) }}</strong><small>Recipients in this view</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon green"><i class="fas fa-circle-check"></i></span><strong>{{ number_format($stats['paid']) }}</strong><small>Payment confirmed</small></div></div>
    <div class="col-12 col-sm-4"><div class="ug-stat"><span class="ug-stat-icon amber"><i class="fas fa-clock"></i></span><strong>{{ number_format($stats['unpaid']) }}</strong><small>Payment pending</small></div></div>
    <div class="col-12"><div class="ug-filter p-3 p-md-4"><div class="d-flex align-items-center justify-content-between mb-3 gap-2"><div><h5 class="mb-1" style="color:#18324b">Find recipients</h5><small class="text-muted">Search by student ID or name, then narrow the list by hall, type, or payment.</small></div><a href="{{ url('hostel recipients') }}" class="btn btn-light btn-sm border"><i class="fas fa-rotate-left me-1"></i>Reset</a></div>
        <form method="GET" action="{{ url('hostel recipients') }}"><div class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-3"><label class="form-label" for="ug-recipient-search">Student search</label><input id="ug-recipient-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="ID number or full name"></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-recipient-hall">Hall</label><select id="ug-recipient-hall" name="hall" class="form-select"><option value="all">All halls</option>@foreach ($hall as $item)<option value="{{ $item->hall }}" @selected(($filters['hall'] ?? '') === $item->hall)>{{ $item->hall }}</option>@endforeach</select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-recipient-type">Assignment type</label><select id="ug-recipient-type" name="bed_type" class="form-select"><option value="all">All types</option>@foreach ($bedTypes as $value => $label)<option value="{{ $value }}" @selected((string)($filters['bed_type'] ?? '') === (string)$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-recipient-payment">Payment</label><select id="ug-recipient-payment" name="hostel_payment" class="form-select"><option value="all">All statuses</option><option value="1" @selected(($filters['hostel_payment'] ?? '') === '1')>Confirmed</option><option value="0" @selected(($filters['hostel_payment'] ?? '') === '0')>Pending</option></select></div>
            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="ug-recipient-date">Allocated after</label><input id="ug-recipient-date" type="datetime-local" name="date" value="{{ $filters['date'] ?? '' }}" class="form-control"></div>
            <div class="col-12 col-md-6 col-lg-1"><button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i>Find</button></div>
        </div></form>
    </div></div>
    <div class="col-12"><div class="ug-table-card"><div class="p-3 d-flex align-items-center justify-content-between gap-2 flex-wrap"><div><h5 class="mb-1" style="color:#18324b">Assigned students</h5><small class="text-muted">A permit becomes available after payment is confirmed.</small></div><span class="badge bg-light text-dark border">{{ number_format($stats['total']) }} record(s)</span></div><div class="table-responsive">
        <table id="ug-recipient-table" class="table table-hover align-middle" style="width:100%"><thead><tr><th>#</th><th>Student</th><th>Location</th><th>Assignment type</th><th>Payment</th><th class="text-end">Actions</th></tr></thead><tbody>
        @forelse ($data as $row)<tr><td>{{ $rowStart + $loop->index }}</td><td class="ug-name">{{ $row->student_name ?: 'Name not found' }}<small>{{ $row->occupant }}</small></td><td>{{ $row->hall }}<small class="text-muted">{{ $row->block }} · Room {{ $row->room }} · Bed {{ $row->bed }}</small></td><td><span class="ug-badge type"><i class="fas fa-tag"></i>{{ $bedTypes[(int)$row->bed_type] ?? 'Other' }}</span></td><td>@if ((int)$row->hostel_payment === 1)<span class="ug-badge paid"><i class="fas fa-circle-check"></i>Confirmed</span>@else<span class="ug-badge pending"><i class="fas fa-clock"></i>Pending</span>@endif</td><td class="text-end"><div class="d-inline-flex gap-1 flex-wrap justify-content-end">@if ((int)$row->hostel_payment === 1)<a href="{{ route('admin.hostel.permit', ['id' => $row->id]) }}" class="btn btn-info btn-sm" target="_blank" rel="noopener"><i class="fas fa-file-pdf me-1"></i>Permit</a>@endif<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $row->id }}">{{ (int)$row->hostel_payment === 1 ? 'Mark pending' : 'Confirm payment' }}</button><button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#revokeModal{{ $row->id }}"><i class="fas fa-user-minus me-1"></i>Revoke</button></div></td></tr>@empty<tr><td colspan="6"><div class="ug-empty"><i class="fas fa-users-slash d-block"></i><strong>No recipients match these filters.</strong><div>Try clearing a filter or searching for another student.</div></div></td></tr>@endforelse
        </tbody></table>
    </div><div class="ug-pagination d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"><small class="text-muted">Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() ?? 0 }} recipients</small><div>{{ $data->onEachSide(1)->links('pagination::bootstrap-4') }}</div></div></div>
</div></div></div>

@foreach ($data as $row)
    <div id="revokeModal{{ $row->id }}" class="modal fade" tabindex="-1" aria-labelledby="revokeLabel{{ $row->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="revokeLabel{{ $row->id }}">Revoke allocation?</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form action="{{ url('revoke') }}" method="POST"><div class="modal-body">@csrf <p class="mb-0">This releases <strong>{{ $row->occupant }}</strong> from {{ $row->hall }}, Room {{ $row->room }}, Bed {{ $row->bed }}. The space becomes available again.</p><input type="hidden" name="id" value="{{ $row->id }}"></div><div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Revoke allocation</button></div></form></div></div></div>
    <div id="paymentModal{{ $row->id }}" class="modal fade" tabindex="-1" aria-labelledby="paymentLabel{{ $row->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="paymentLabel{{ $row->id }}">Update payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form action="{{ url('hostel-payment') }}" method="POST"><div class="modal-body">@csrf <p class="mb-0">Update payment for <strong>{{ $row->occupant }}</strong>.</p><input type="hidden" name="id" value="{{ $row->id }}"><input type="hidden" name="hostel_payment" value="{{ (int)$row->hostel_payment === 1 ? 0 : 1 }}"></div><div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">{{ (int)$row->hostel_payment === 1 ? 'Mark pending' : 'Confirm payment' }}</button></div></form></div></div></div>
@endforeach
