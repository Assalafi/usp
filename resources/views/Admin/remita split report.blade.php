@php
    $money = fn ($value) => '₦' . number_format((float) $value, 2);
    $value = fn ($key, $fallback = '') => e($filters[$key] ?? $fallback);
    $gross = (float) ($summary->gross_amount ?? 0);
    $base = (float) ($summary->base_amount ?? 0);
    $surcharge = (float) ($summary->surcharge_amount ?? 0);
    $discrepancy = $gross - $allocatedTotal;
@endphp

<style>
    .split-report { --finance-ink:#15263b; --finance-blue:#1769aa; --finance-soft:#eef6fc; }
    .split-report .report-hero { background:linear-gradient(120deg,#103b63,#1769aa 62%,#28a8c7); color:#fff; border:0; border-radius:20px; overflow:hidden; }
    .split-report .report-hero .eyebrow { letter-spacing:.12em; text-transform:uppercase; font-size:.72rem; opacity:.78; font-weight:700; }
    .split-report .filter-card, .split-report .panel { border:1px solid #e4edf5; border-radius:16px; box-shadow:0 8px 24px rgba(27,63,93,.06); }
    .split-report .filter-card { background:#fff; }
    .split-report .metric { border-radius:15px; border:1px solid #e5edf4; background:#fff; min-height:116px; padding:18px; position:relative; overflow:hidden; }
    .split-report .metric:after { content:""; width:66px; height:66px; border-radius:50%; background:var(--metric-bg,#eef6fc); position:absolute; right:-22px; top:-20px; }
    .split-report .metric .label { color:#6b7d90; font-size:.76rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; }
    .split-report .metric .value { color:var(--finance-ink); font-size:1.35rem; line-height:1.2; font-weight:800; margin-top:8px; }
    .split-report .metric .hint { color:#8090a1; font-size:.78rem; margin-top:7px; }
    .split-report .table thead th { background:#f5f9fc; color:#557089; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .split-report .table td { vertical-align:middle; color:#253b50; }
    .split-report .table-responsive { border-radius:12px; }
    .split-report .status-pill { border-radius:999px; padding:.34rem .65rem; font-size:.72rem; font-weight:700; }
    .split-report .status-paid { background:#e8f7ef; color:#167345; }
    .split-report .status-pending { background:#fff5df; color:#966b0b; }
    .split-report .status-other { background:#f0f2f5; color:#657385; }
    .split-report .section-title { color:var(--finance-ink); font-weight:800; }
    .split-report .small-muted { color:#718398; font-size:.82rem; }
    .split-report .trend-row { border-bottom:1px solid #eef2f5; padding:.45rem 0; }
    .split-report .trend-row:last-child { border-bottom:0; }
    @media (max-width:767px) { .split-report .report-hero { border-radius:14px; } .split-report .metric .value { font-size:1.1rem; } .split-report .filter-card .btn { width:100%; } }
</style>

<div class="split-report container-fluid py-2">
    <div class="report-hero p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="eyebrow">Finance control centre</div>
                <h2 class="mb-2 mt-1">Remita split payment report</h2>
                <p class="mb-0 opacity-75">Track gross collections, service charges and the exact amounts assigned to each beneficiary.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <span class="badge bg-light text-primary rounded-pill px-3 py-2"><i class="fas fa-calendar-alt me-1"></i>{{ $periodLabel }}</span>
                <div class="small mt-2 opacity-75">Invoice date basis · split-enabled payments only</div>
            </div>
        </div>
    </div>

    <form method="GET" class="filter-card p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div><h5 class="section-title mb-1"><i class="fas fa-sliders-h text-primary me-2"></i>Report filters</h5><div class="small-muted">Choose a period, then narrow the report by service, academic entity or payment status.</div></div>
            <a class="btn btn-sm btn-outline-secondary mt-2 mt-md-0" href="{{ url('/admin/remita-split-report') }}"><i class="fas fa-undo me-1"></i>Reset</a>
        </div>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Reporting period</label><select class="form-select" name="period" id="report-period">
                @foreach(['all'=>'All time','daily'=>'Daily · last 30 days','weekly'=>'Weekly · last 12 weeks','monthly'=>'Monthly · last 12 months','quarterly'=>'Quarterly · last 8 quarters','yearly'=>'Yearly · last 5 years','custom'=>'Custom date range'] as $k=>$label)
                    <option value="{{ $k }}" @selected($period === $k)>{{ $label }}</option>
                @endforeach
            </select></div>
            <div class="col-sm-6 col-lg-2 custom-date"><label class="form-label small fw-bold">From</label><input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}"></div>
            <div class="col-sm-6 col-lg-2 custom-date"><label class="form-label small fw-bold">To</label><input class="form-control" type="date" name="date_to" value="{{ $dateTo }}"></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Payment status</label><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['Paid','Pending','Cancelled','Failed'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Payment service</label><select class="form-select" name="service"><option value="">All services</option>@foreach($services as $service)<option value="{{ $service['key'] ?? '' }}" @selected(($filters['service'] ?? '') === ($service['key'] ?? ''))>{{ $service['label'] ?? ($service['name'] ?? ($service['key'] ?? 'Service')) }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Sponsor</label><select class="form-select" name="sponsor"><option value="">All sponsors</option><option value="nelfund" @selected(($filters['sponsor'] ?? '') === 'nelfund')>NELFUND</option><option value="self" @selected(($filters['sponsor'] ?? '') === 'self')>Self sponsored</option></select></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label small fw-bold">Faculty / college</label><select class="form-select" name="faculty" id="report-faculty"><option value="">All faculties</option>@foreach($faculties as $faculty)<option value="{{ $faculty->code }}" @selected(($filters['faculty'] ?? '') === $faculty->code)>{{ $faculty->title }} ({{ $faculty->code }})</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label small fw-bold">Department</label><select class="form-select" name="department" id="report-department"><option value="">All departments</option>@foreach($departments as $department)<option data-faculty="{{ $department->faculty }}" value="{{ $department->code }}" @selected(($filters['department'] ?? '') === $department->code)>{{ $department->title }} ({{ $department->code }})</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label small fw-bold">Programme</label><select class="form-select" name="program" id="report-program"><option value="">All programmes</option>@foreach($programs as $program)<option data-faculty="{{ $program->faculty }}" data-department="{{ $program->department }}" value="{{ $program->code }}" @selected(($filters['program'] ?? '') === $program->code)>{{ $program->title }} ({{ $program->code }})</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-1"><label class="form-label small fw-bold">Level</label><select class="form-select" name="level"><option value="">All</option>@foreach(['100','200','300','400','500','600','700','800'] as $level)<option value="{{ $level }}" @selected(($filters['level'] ?? '') === $level)>{{ $level }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Academic session</label><select class="form-select" name="session"><option value="">All sessions</option>@foreach($sessions as $session)<option value="{{ $session }}" @selected(($filters['session'] ?? '') === $session)>{{ $session }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">Student ID / name</label><input class="form-control" name="student_id" value="{{ $value('student_id') }}" placeholder="e.g. 22/08/08/0010"></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label small fw-bold">RRR / order reference</label><input class="form-control" name="rrr" value="{{ $value('rrr') }}" placeholder="Search reference"></div>
            <div class="col-sm-6 col-lg-2 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Apply filters</button></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach([
            ['Split invoices', number_format((int) ($summary->invoice_count ?? 0)), 'Invoices included in this view', 'fa-file-invoice-dollar', '#eef6fc'],
            ['Gross billed', $money($gross), 'Base payment plus service charge', 'fa-wallet', '#eaf8f1'],
            ['Paid gross', $money($paid), 'Successfully settled invoices', 'fa-check-circle', '#eaf8f1'],
            ['Pending gross', $money($pending), 'Awaiting payment or confirmation', 'fa-hourglass-half', '#fff5df'],
            ['Service charges', $money($surcharge), 'Collected for split allocation', 'fa-layer-group', '#f4efff'],
            ['Allocated to beneficiaries', $money($allocatedTotal), 'Remita line-item total', 'fa-sitemap', '#edf4ff'],
        ] as $metric)
            <div class="col-6 col-xl-2"><div class="metric" style="--metric-bg:{{ $metric[4] }}"><div class="label"><i class="fas {{ $metric[3] }} me-1 text-primary"></i>{{ $metric[0] }}</div><div class="value">{{ $metric[1] }}</div><div class="hint">{{ $metric[2] }}</div></div></div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7"><div class="panel p-3 p-lg-4 h-100"><div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="section-title mb-1">Collection trend</h5><div class="small-muted">{{ $periodLabel }} · invoice count, gross amount and service charge</div></div><span class="badge bg-light text-dark">{{ ucfirst($period) }}</span></div>
            @forelse($trend as $item)<div class="trend-row d-flex justify-content-between gap-3"><span class="fw-semibold">{{ $item->bucket }}</span><span class="text-end"><b>{{ $money($item->gross_amount) }}</b><small class="text-muted d-block">{{ number_format($item->invoice_count) }} invoices · {{ $money($item->surcharge_amount) }} charge</small></span></div>@empty<div class="text-center text-muted py-5"><i class="fas fa-chart-line fa-2x mb-2"></i><div>No split payments match these filters.</div></div>@endforelse
        </div></div>
        <div class="col-xl-5"><div class="panel p-3 p-lg-4 h-100"><h5 class="section-title mb-1">Reconciliation check</h5><div class="small-muted mb-3">The allocation total should equal the gross amount for split invoices.</div><div class="d-flex justify-content-between py-2 border-bottom"><span>Base payment</span><b>{{ $money($base) }}</b></div><div class="d-flex justify-content-between py-2 border-bottom"><span>Additional service charge</span><b>{{ $money($surcharge) }}</b></div><div class="d-flex justify-content-between py-2 border-bottom"><span>Gross invoice total</span><b>{{ $money($gross) }}</b></div><div class="d-flex justify-content-between py-2"><span>Beneficiary allocations</span><b>{{ $money($allocatedTotal) }}</b></div><div class="alert {{ abs($discrepancy) < .01 ? 'alert-success' : 'alert-warning' }} mt-3 mb-0"><i class="fas {{ abs($discrepancy) < .01 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-1"></i>{{ abs($discrepancy) < .01 ? 'Balanced: every split amount is accounted for.' : 'Review needed: allocation difference is ' . $money(abs($discrepancy)) . '.' }}</div></div></div>
    </div>

    <div class="panel p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div><h5 class="section-title mb-1"><i class="fas fa-university text-primary me-2"></i>Destination accounts</h5><div class="small-muted">The current Remita settlement accounts and how each additional charge is shared.</div></div>
            <span class="badge {{ !empty($splitSettings['enabled']) ? 'bg-success' : 'bg-secondary' }} rounded-pill">{{ !empty($splitSettings['enabled']) ? 'Split enabled' : 'Split currently disabled' }}</span>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Account / beneficiary</th><th>Account number</th><th>Bank code</th><th class="text-end">Share</th><th class="text-end">Current charge allocation</th></tr></thead><tbody>
            @foreach(($splitSettings['beneficiaries'] ?? []) as $beneficiary)
                <tr><td><span class="fw-bold">{{ $beneficiary['label'] ?? 'Beneficiary' }}</span><small class="d-block text-muted">{{ ($beneficiary['key'] ?? '') === 'primary' ? 'Primary Remita account · receives the base payment' : 'Additional Remita account' }}</small></td><td class="font-monospace">{{ $beneficiary['account_number'] ?? 'Not configured' }}</td><td class="font-monospace">{{ $beneficiary['bank_code'] ?? '—' }}</td><td class="text-end fw-bold">{{ number_format((float) ($beneficiary['percentage'] ?? 0), 2) }}%</td><td class="text-end">{{ $money($surcharge * ((float) ($beneficiary['percentage'] ?? 0) / 100)) }}</td></tr>
            @endforeach
            @if(empty($splitSettings['beneficiaries']))<tr><td colspan="5" class="text-center text-muted py-4">No beneficiary accounts are configured.</td></tr>@endif
        </tbody></table></div>
        <div class="small-muted mt-3"><i class="fas fa-lock me-1"></i>Historical transaction rows show masked account numbers. This prevents old invoices from exposing account details while preserving the configured destination accounts above.</div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4"><div class="panel p-3 h-100"><h5 class="section-title mb-3">Beneficiary settlement</h5><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Beneficiary / account</th><th class="text-end">Invoices</th><th class="text-end">Amount</th></tr></thead><tbody>@forelse($allocationSummary as $item)<tr><td><span class="fw-semibold">{{ $item->beneficiary_name }}</span><small class="d-block text-muted">{{ $item->is_primary ? 'Primary account' : 'Additional account' }} · {{ $item->beneficiary_account ?: 'Account masked' }} · {{ $item->bank_code }}</small></td><td class="text-end">{{ number_format($item->invoice_count) }}</td><td class="text-end fw-bold">{{ $money($item->allocated_amount) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No allocations yet.</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-lg-4"><div class="panel p-3 h-100"><h5 class="section-title mb-3">By payment status</h5><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Status</th><th class="text-end">Invoices</th><th class="text-end">Gross</th></tr></thead><tbody>@forelse($statusSummary as $item)@php $statusClass=in_array(strtolower((string)$item->status),['paid','successful','success'],true)?'status-paid':(in_array(strtolower((string)$item->status),['pending','processing'],true)?'status-pending':'status-other'); @endphp<tr><td><span class="status-pill {{ $statusClass }}">{{ ucfirst($item->status ?: 'Unknown') }}</span></td><td class="text-end">{{ number_format($item->invoice_count) }}</td><td class="text-end fw-bold">{{ $money($item->gross_amount) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No status data.</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-lg-4"><div class="panel p-3 h-100"><h5 class="section-title mb-3">By service</h5><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Service</th><th class="text-end">Invoices</th><th class="text-end">Charge</th></tr></thead><tbody>@forelse($serviceSummary as $item)<tr><td class="fw-semibold">{{ $item->description ?: 'Unnamed service' }}</td><td class="text-end">{{ number_format($item->invoice_count) }}</td><td class="text-end fw-bold">{{ $money($item->surcharge_amount) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No service data.</td></tr>@endforelse</tbody></table></div></div></div>
    </div>

    <div class="panel p-3 p-lg-4 mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h5 class="section-title mb-1">Split payment transactions</h5><div class="small-muted">{{ number_format($rows->total()) }} matching invoice records · showing {{ $rows->count() }} on this page</div></div><span class="badge bg-light text-primary">{{ $periodLabel }}</span></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Invoice</th><th>Student</th><th>Service</th><th>Academic entity</th><th class="text-end">Base</th><th class="text-end">Charge</th><th class="text-end">Gross</th><th>Status</th><th>Date</th></tr></thead><tbody>@forelse($rows as $row)@php $statusClass=in_array(strtolower((string)$row->status),['paid','successful','success'],true)?'status-paid':(in_array(strtolower((string)$row->status),['pending','processing'],true)?'status-pending':'status-other'); @endphp<tr><td><span class="fw-bold">{{ $row->rrr ?: 'Pending RRR' }}</span><small class="d-block text-muted">#{{ $row->id }}</small></td><td><span class="fw-semibold">{{ $row->matric_number ?: $row->username ?: '—' }}</span><small class="d-block text-muted">{{ $row->fullname ?: $row->name ?? 'Student' }}</small></td><td>{{ $row->description ?: 'Payment' }}</td><td><span class="fw-semibold">{{ $row->faculty_title ?: $row->faculty ?: '—' }}</span><small class="d-block text-muted">{{ $row->department_title ?: $row->department ?: '—' }} · {{ $row->program_title ?: $row->program ?: '—' }} · {{ $row->level ?: '—' }}</small></td><td class="text-end">{{ $money($row->base_amount ?? ((float)$row->amount - (float)$row->surcharge_amount)) }}</td><td class="text-end">{{ $money($row->surcharge_amount) }}</td><td class="text-end fw-bold">{{ $money($row->amount) }}</td><td><span class="status-pill {{ $statusClass }}">{{ ucfirst($row->status ?: 'Unknown') }}</span></td><td class="text-nowrap">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '—' }}</td></tr>@empty<tr><td colspan="9" class="text-center text-muted py-5">No split payment transactions match the selected filters.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $rows->links() }}</div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const period = document.getElementById('report-period');
    const dateFields = document.querySelectorAll('.custom-date');
    const refreshDates = () => dateFields.forEach(el => el.style.display = period.value === 'custom' ? '' : 'none');
    period?.addEventListener('change', refreshDates); refreshDates();
    const faculty = document.getElementById('report-faculty');
    const department = document.getElementById('report-department');
    const program = document.getElementById('report-program');
    const filterOptions = (select, attributes, values) => Array.from(select.options).forEach(option => {
        if (!option.value) { option.hidden = false; return; }
        option.hidden = attributes.some((attribute, index) => values[index] && option.dataset[attribute] !== values[index]);
        if (option.hidden && option.selected) { option.selected = false; select.value = ''; }
    });
    const cascade = () => { filterOptions(department, ['faculty'], [faculty.value]); filterOptions(program, ['faculty','department'], [faculty.value, department.value]); };
    faculty?.addEventListener('change', cascade); department?.addEventListener('change', cascade); cascade();
});
</script>
