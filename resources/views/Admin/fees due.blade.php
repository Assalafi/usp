<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row"><!-- Fees dashboard -->
<style>
    .fees-dashboard{--fees-blue:#1769aa;--fees-ink:#172b4d;--fees-muted:#65758b;--fees-border:#e7edf5;--fees-bg:#f5f8fc;margin:-8px -8px 22px}
    .fees-dashboard *{box-sizing:border-box}
    .fees-hero{border:0;border-radius:20px;background:linear-gradient(135deg,#123b67 0%,#1769aa 55%,#24a6c8 100%);color:#fff;padding:24px 26px;box-shadow:0 14px 34px rgba(22,67,111,.18);position:relative;overflow:hidden}
    .fees-hero:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;right:-80px;top:-130px;background:rgba(255,255,255,.12)}
    .fees-hero h2{font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin:0 0 6px;position:relative;z-index:1}.fees-hero p{margin:0;color:rgba(255,255,255,.78);position:relative;z-index:1}
    .fees-hero-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;position:relative;z-index:1}.fees-hero-actions .btn{border-radius:10px;font-weight:700;padding:.55rem .8rem}
    .fees-session-pill{display:inline-flex;align-items:center;gap:7px;margin-top:15px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:.82rem;position:relative;z-index:1}
    .fees-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px}.fees-kpi{background:#fff;border:1px solid var(--fees-border);border-radius:15px;padding:17px 18px;box-shadow:0 5px 18px rgba(24,49,80,.06);min-width:0}.fees-kpi-label{color:var(--fees-muted);font-size:.77rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.fees-kpi-value{font-size:1.25rem;color:var(--fees-ink);font-weight:800;margin-top:7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.fees-kpi-note{color:#8a97a8;font-size:.76rem;margin-top:5px}.fees-kpi.blue{border-top:4px solid #1769aa}.fees-kpi.green{border-top:4px solid #159570}.fees-kpi.amber{border-top:4px solid #de9b16}.fees-kpi.red{border-top:4px solid #d9534f}
    .fees-panel{background:#fff;border:1px solid var(--fees-border);border-radius:16px;padding:18px;box-shadow:0 5px 18px rgba(24,49,80,.05);height:100%}.fees-panel-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:15px}.fees-panel-title h5{margin:0;color:var(--fees-ink);font-size:1rem;font-weight:800}.fees-panel-title small{color:var(--fees-muted)}
    .fees-sponsor-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.fees-sponsor{border:1px solid var(--fees-border);border-radius:13px;padding:15px}.fees-sponsor.nelfund{background:linear-gradient(145deg,#f1f8ff,#fff);border-left:4px solid #1769aa}.fees-sponsor.self{background:linear-gradient(145deg,#f1fbf7,#fff);border-left:4px solid #159570}.fees-sponsor-head{display:flex;align-items:center;justify-content:space-between;gap:8px}.fees-sponsor-name{font-weight:800;color:var(--fees-ink)}.fees-sponsor-icon{width:32px;height:32px;display:grid;place-items:center;border-radius:9px;background:#e5f1fb;color:#1769aa}.fees-sponsor.self .fees-sponsor-icon{background:#e2f7ee;color:#159570}.fees-sponsor-amount{font-size:1.2rem;font-weight:800;color:var(--fees-ink);margin-top:12px}.fees-sponsor-meta{display:flex;justify-content:space-between;gap:8px;font-size:.77rem;color:var(--fees-muted);margin-top:5px}.fees-progress{height:6px;border-radius:99px;background:#edf1f6;margin-top:12px;overflow:hidden}.fees-progress span{height:100%;display:block;border-radius:inherit;background:#1769aa}.fees-sponsor.self .fees-progress span{background:#159570}
    .fees-table{width:100%;border-collapse:separate;border-spacing:0}.fees-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#748398;background:#f7f9fc;border-top:1px solid var(--fees-border);border-bottom:1px solid var(--fees-border);padding:10px 11px;white-space:nowrap}.fees-table td{padding:10px 11px;border-bottom:1px solid #eff3f7;color:#34465c;font-size:.82rem;vertical-align:middle}.fees-table tr:last-child td{border-bottom:0}.fees-table .amount{font-weight:800;white-space:nowrap;color:var(--fees-ink)}.fees-table .muted{color:#8a97a8}
    .fees-filter{background:#fff;border:1px solid var(--fees-border);border-radius:16px;box-shadow:0 5px 18px rgba(24,49,80,.05);padding:18px;margin-top:16px}.fees-filter .form-label{font-size:.75rem;font-weight:700;color:#52657b;margin-bottom:5px}.fees-filter .form-control,.fees-filter .form-select{border-color:#dce5ef;border-radius:9px;min-height:40px;font-size:.84rem}.fees-filter .btn{border-radius:9px;min-height:40px;font-weight:700}.fees-filter-heading{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}.fees-filter-heading h5{margin:0;color:var(--fees-ink);font-size:1rem;font-weight:800}.fees-filter-heading small{color:var(--fees-muted)}
    .fees-results-info{font-size:.82rem}.fees-pagination-wrap .pagination{margin:0;gap:4px}.fees-pagination-wrap .page-item .page-link{border:1px solid #dfe7f1;border-radius:8px;color:#36516d;font-size:.8rem;font-weight:700;min-width:34px;text-align:center;padding:.45rem .65rem}.fees-pagination-wrap .page-item.active .page-link{background:#1769aa;border-color:#1769aa;color:#fff}.fees-pagination-wrap .page-item.disabled .page-link{color:#a5b1bf;background:#f7f9fc}@media(max-width:575px){.fees-pagination-row>[class*=col-]{width:100%}.fees-pagination-wrap{justify-content:flex-start!important;margin-top:10px;overflow-x:auto;padding-bottom:3px}.fees-pagination-wrap .pagination{flex-wrap:nowrap}.fees-results-info{font-size:.75rem}}
    .fees-section{margin-top:16px}.fees-section .card{border:1px solid var(--fees-border);border-radius:16px;box-shadow:0 5px 18px rgba(24,49,80,.05);overflow:hidden}.fees-section .card-block{padding:0}.fees-section .table-responsive{border-radius:inherit}.fees-section .table{margin-bottom:0}.fees-section .table thead th{background:#f7f9fc;color:#667890;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--fees-border)}
    @media(max-width:991px){.fees-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.fees-hero-actions{justify-content:flex-start;margin-top:16px}.fees-panel{margin-bottom:14px}}
    @media(max-width:575px){.fees-dashboard{margin:0 -2px 18px}.fees-hero{padding:20px 17px;border-radius:15px}.fees-hero h2{font-size:1.22rem}.fees-hero-actions .btn{font-size:.75rem;padding:.48rem .62rem}.fees-kpis{grid-template-columns:1fr 1fr;gap:9px}.fees-kpi{padding:13px 12px;border-radius:12px}.fees-kpi-value{font-size:1rem}.fees-kpi-note{font-size:.68rem}.fees-sponsor-grid{grid-template-columns:1fr}.fees-panel{padding:14px;border-radius:13px}.fees-table th,.fees-table td{padding:8px 7px}.fees-filter{padding:14px;border-radius:13px}.fees-filter-heading{align-items:flex-start;gap:10px}.fees-filter-heading small{font-size:.7rem}.fees-filter .row>[class*=col-]{margin-bottom:9px}}
</style>

@php
    $schoolFeeTotals = $school_fee_totals ?? [];
    $sponsorSummary = $fee_sponsor_summary ?? [];
    $unpaidSummary = $fee_unpaid_summary ?? [];
    $levelSummary = $fee_level_summary ?? [];
    $nelfund = $sponsorSummary['nelfund'] ?? ['students' => 0, 'required_amount' => 0, 'amount_paid' => 0, 'outstanding_amount' => 0, 'fully_paid' => 0];
    $selfSponsor = $sponsorSummary['self'] ?? ['students' => 0, 'required_amount' => 0, 'amount_paid' => 0, 'outstanding_amount' => 0, 'fully_paid' => 0];
    $nelfundRate = ($nelfund['required_amount'] ?? 0) > 0 ? min(100, (($nelfund['amount_paid'] ?? 0) / $nelfund['required_amount']) * 100) : 0;
    $selfRate = ($selfSponsor['required_amount'] ?? 0) > 0 ? min(100, (($selfSponsor['amount_paid'] ?? 0) / $selfSponsor['required_amount']) * 100) : 0;
    $paymentRecords = (int) collect($payment_summary ?? [])->sum('count');
@endphp

<div class="fees-dashboard">
    <div class="fees-hero">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h2><i class="fas fa-wallet me-2"></i>Fees &amp; payment control</h2>
                <p>Review programme-fee collections, NELFUND support, self-sponsored payments and active outstanding balances in one place.</p>
                <span class="fees-session-pill"><i class="far fa-calendar-alt"></i> Session {{ $fees_session ?? '—' }}</span>
            </div>
            <div class="col-lg-5">
                <div class="fees-hero-actions">
                    <a href="/admin/receipts" class="btn btn-light"><i class="fas fa-file-pdf me-1"></i> Receipts</a>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#exportPaidStudentsModal"><i class="fas fa-file-excel me-1"></i> Paid export</button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exportUnpaidStudentsModal"><i class="fas fa-user-clock me-1"></i> Unpaid export</button>
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#bulkVerifyModal"><i class="fas fa-check-circle me-1"></i> Verify RRR</button>
                </div>
            </div>
        </div>
    </div>

    <div class="fees-kpis">
        <div class="fees-kpi blue"><div class="fees-kpi-label">Total collected</div><div class="fees-kpi-value">N{{ number_format((float) ($payment_summary_total ?? 0), 2) }}</div><div class="fees-kpi-note">All paid payment descriptions, excluding refunds</div></div>
        <div class="fees-kpi red"><div class="fees-kpi-label">Active outstanding</div><div class="fees-kpi-value">N{{ number_format((float) ($unpaidSummary['outstanding_amount'] ?? 0), 2) }}</div><div class="fees-kpi-note">{{ number_format((int) ($unpaidSummary['students'] ?? 0)) }} active student{{ (($unpaidSummary['students'] ?? 0) == 1) ? '' : 's' }} still owing</div></div>
        <div class="fees-kpi green"><div class="fees-kpi-label">Programme fees paid</div><div class="fees-kpi-value">N{{ number_format((float) ($schoolFeeTotals['amount_paid'] ?? 0), 2) }}</div><div class="fees-kpi-note">Validated school-fee collection</div></div>
        <div class="fees-kpi amber"><div class="fees-kpi-label">Paid payment records</div><div class="fees-kpi-value">{{ number_format($paymentRecords) }}</div><div class="fees-kpi-note">Across all services in this session</div></div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-5">
            <div class="fees-panel">
                <div class="fees-panel-title"><div><h5>Sponsor breakdown</h5><small>Paid programme fees split by invoice sponsor</small></div><i class="fas fa-chart-pie text-primary"></i></div>
                <div class="fees-sponsor-grid">
                    <div class="fees-sponsor nelfund">
                        <div class="fees-sponsor-head"><span class="fees-sponsor-name">NELFUND</span><span class="fees-sponsor-icon"><i class="fas fa-graduation-cap"></i></span></div>
                        <div class="fees-sponsor-amount">N{{ number_format((float) ($nelfund['amount_paid'] ?? 0), 2) }}</div>
                        <div class="fees-sponsor-meta"><span>{{ number_format((int) ($nelfund['students'] ?? 0)) }} students</span><span>{{ number_format((int) ($nelfund['fully_paid'] ?? 0)) }} complete</span></div>
                        <div class="fees-progress"><span style="width:{{ number_format($nelfundRate, 2, '.', '') }}%"></span></div>
                    </div>
                    <div class="fees-sponsor self">
                        <div class="fees-sponsor-head"><span class="fees-sponsor-name">Self sponsor</span><span class="fees-sponsor-icon"><i class="fas fa-user-check"></i></span></div>
                        <div class="fees-sponsor-amount">N{{ number_format((float) ($selfSponsor['amount_paid'] ?? 0), 2) }}</div>
                        <div class="fees-sponsor-meta"><span>{{ number_format((int) ($selfSponsor['students'] ?? 0)) }} students</span><span>{{ number_format((int) ($selfSponsor['fully_paid'] ?? 0)) }} complete</span></div>
                        <div class="fees-progress"><span style="width:{{ number_format($selfRate, 2, '.', '') }}%"></span></div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Sponsor cards use paid school-fee invoices. The overall outstanding figure also includes active students with no paid invoice yet.</small>
                <div class="mt-3 p-3 rounded-3" style="background:#f7f9fc"><div class="d-flex justify-content-between gap-2"><span class="text-muted small">Active unpaid fees</span><strong class="text-danger">N{{ number_format((float) ($unpaidSummary['outstanding_amount'] ?? 0), 2) }}</strong></div></div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="fees-panel">
                <div class="fees-panel-title"><div><h5>Collection and outstanding by level</h5><small>Paid collection and active balances for every level</small></div><i class="fas fa-layer-group text-danger"></i></div>
                <div class="table-responsive"><table class="fees-table"><thead><tr><th>Level</th><th>Paid students</th><th>Paid amount</th><th>Unpaid students</th><th>Outstanding</th></tr></thead><tbody>
                    @forelse ($levelSummary as $level)
                        <tr><td><span class="badge bg-light text-dark">{{ $level['level'] ?: 'Not set' }}</span></td><td>{{ number_format((int) ($level['paid_students'] ?? 0)) }}</td><td class="amount">N{{ number_format((float) ($level['paid_amount'] ?? 0), 2) }}</td><td>{{ number_format((int) ($level['unpaid_students'] ?? 0)) }}</td><td class="amount text-danger">N{{ number_format((float) ($level['outstanding_amount'] ?? 0), 2) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center muted py-4">No level collection or outstanding records found for this session.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="fees-panel">
                <div class="fees-panel-title"><div><h5>Paid payments by description</h5><small>Every paid invoice except refunds</small></div><i class="fas fa-list-alt text-primary"></i></div>
                <div class="table-responsive"><table class="fees-table"><thead><tr><th>Description</th><th>Records / students</th><th class="text-end">Amount</th></tr></thead><tbody>
                    @forelse (($payment_summary ?? collect()) as $summary)
                        <tr><td class="text-wrap" style="min-width:180px">{{ $summary['description'] }}</td><td>{{ number_format((int) ($summary['count'] ?? 0)) }}</td><td class="amount text-end">N{{ number_format((float) $summary['amount'], 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center muted py-4">No paid payments found for this session.</td></tr>
                    @endforelse
                    <tr><td><strong>Total collected</strong></td><td><strong>{{ number_format((int) collect($payment_summary ?? [])->sum('count')) }}</strong></td><td class="amount text-end"><strong>N{{ number_format((float) ($payment_summary_total ?? 0), 2) }}</strong></td></tr>
                </tbody></table></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="fees-panel">
                <div class="fees-panel-title"><div><h5>Find payment records</h5><small>Use a session, sponsor, student ID, RRR or status</small></div><i class="fas fa-filter text-primary"></i></div>
                <form class="needs-validation fees-filter-form" novalidate method="GET" action="{{ url()->current() }}">
                    @csrf
                    <div class="row gx-2">
                        <div class="col-md-6 mb-2"><label class="form-label" for="session">Session</label><select class="form-select" id="session" name="session"><option value="{{ $_GET['session'] ?? ($fees_session ?? '') }}">{{ $_GET['session'] ?? ($fees_session ?? 'Select session') }}</option>@foreach (($session ?? collect()) as $sessionRow)<option value="{{ $sessionRow->title }}">{{ $sessionRow->title }}</option>@endforeach</select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="student_id">Student ID</label><input type="search" class="form-control" id="student_id" name="student_id" placeholder="e.g. 22/08/08/0010" value="{{ $_GET['student_id'] ?? '' }}" autocomplete="off"></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="facultyf">Faculty</label><select class="form-select faculty" lang="f" name="faculty" id="facultyf"><option value="{{ $_GET['faculty'] ?? '' }}">{{ $_GET['faculty'] ?? 'All faculties' }}</option>@foreach (($faculty ?? collect()) as $roww)<option value="{{ $roww->code }}">{{ $roww->title }} ({{ $roww->code }})</option>@endforeach</select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="departmentf">Department</label><select class="form-select department" lang="f" id="departmentf" name="department"><option value="{{ $_GET['department'] ?? '' }}">{{ $_GET['department'] ?? 'Select faculty first' }}</option></select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="programf">Programme</label><select class="form-select" id="programf" lang="f" name="program"><option value="{{ $_GET['program'] ?? '' }}">{{ $_GET['program'] ?? 'Select department first' }}</option></select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="description">Payment description</label><select class="form-select" id="description" name="description"><option value="{{ $_GET['description'] ?? '' }}">{{ $_GET['description'] ?? 'All descriptions' }}</option>@foreach (($payment_summary ?? collect()) as $summary)<option value="{{ $summary['filter'] ?? $summary['description'] }}">{{ $summary['description'] }}</option>@endforeach</select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="rrr">RRR</label><input type="text" class="form-control" id="rrr" name="rrr" placeholder="Enter RRR" value="{{ $_GET['rrr'] ?? '' }}"></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="{{ $_GET['status'] ?? '' }}">{{ $_GET['status'] ?? 'All statuses' }}</option><option value="Paid">Paid</option><option value="Pending">Pending</option></select></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="start">From date</label><input type="date" name="start" id="start" class="form-control" value="{{ $_GET['start'] ?? '2024-01-01' }}"></div>
                        <div class="col-md-6 mb-2"><label class="form-label" for="end">To date</label><input type="date" name="end" id="end" class="form-control" value="{{ $_GET['end'] ?? date('Y-m-d') }}"></div>
                        <div class="col-12 mt-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search payments</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
            <div class="col-sm-12 fees-section">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div><h5 class="mb-1"><i class="fas fa-receipt me-2 text-primary"></i>Payment transactions</h5><small class="text-muted">Showing up to 100 records for the selected session and filters.</small></div>
                        <span class="badge bg-light text-dark"><i class="fas fa-database me-1"></i>Live records</span>
                    </div>
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'NAME' }}</th>
                                        <th>{{ 'AMOUNT' }}</th>
                                        <th>{{ 'RRR' }}</th>
                                        <th>{{ 'DESCRIPTION' }}</th>
                                        <th>{{ 'PHONE' }}</th>
                                        <th>{{ 'STATUS' }}</th>
                                        <th>{{ 'DATE' }}</th>
                                        <th>{{ 'ACTION' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>N{{ number_format($row->amount, 2) }}</td>
                                            <td>{{ $row->rrr }}</td>
                                            <td>{{ $row->description }}</td>
                                            <td>{{ $row->phone }}</td>
                                            <td>{{ $row->status }}</td>
                                            <td>{{ date('d-m-y', strtotime($row->updated_at)) }}</td>
                                            <td>

                                                <button type="button"
                                                    class="btn btn-icon btn-primary btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSessionModal{{ $row->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="editSessionModal{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="editSessionModalLabel{{ $row->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editSessionModalLabel{{ $row->id }}">Update Session</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="update {{ $page }}" method="POST">
                                                        <div class="modal-body">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="session{{ $row->id }}">Session</label>
                                                                <select class="form-control" id="session{{ $row->id }}" name="session" required>
                                                                    <option value="">Select Session</option>
                                                                    <option value="2028/2029" {{ $row->session == '2028/2029' ? 'selected' : '' }}>2028/2029</option>
                                                                    <option value="2027/2028" {{ $row->session == '2027/2028' ? 'selected' : '' }}>2027/2028</option>
                                                                    <option value="2026/2027" {{ $row->session == '2026/2027' ? 'selected' : '' }}>2026/2027</option>
                                                                    <option value="2025/2026" {{ $row->session == '2025/2026' ? 'selected' : '' }}>2025/2026</option>
                                                                    <option value="2024/2025" {{ $row->session == '2024/2025' ? 'selected' : '' }}>2024/2025</option>
                                                                    <option value="2023/2024" {{ $row->session == '2023/2024' ? 'selected' : '' }}>2023/2024</option>
                                                                    <option value="2022/2023" {{ $row->session == '2022/2023' ? 'selected' : '' }}>2022/2023</option>
                                                                    <option value="2021/2022" {{ $row->session == '2021/2022' ? 'selected' : '' }}>2021/2022</option>
                                                                    <option value="2020/2021" {{ $row->session == '2020/2021' ? 'selected' : '' }}>2020/2021</option>
                                                                    <option value="2019/2020" {{ $row->session == '2019/2020' ? 'selected' : '' }}>2019/2020</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure</h4>
                                                        </div>
                                                        <form class="form-group" action="delete {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if (method_exists($data, 'links'))
                            <div class="row mt-4 fees-pagination-row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted fees-results-info">
                                            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }}
                                            of {{ $data->total() ?? 0 }} results
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end fees-pagination-wrap">
                                        {{ $data->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif                        <!-- [ Data table ] end -->
                    </div>
                </div>

<!-- Offline Payment Creation Section -->
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-money-check-alt me-2"></i>Create Offline Payment</h5>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#offlinePaymentSection">
                <i class="fas fa-chevron-down"></i> Toggle
            </button>
        </div>
        <div class="collapse show" id="offlinePaymentSection">
            <div class="card-block">
                <p class="text-muted mb-3">For students/applicants who already paid offline (cash, bank transfer). Creating a payment here allows them to skip the online payment step.</p>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" id="offlineSearch" class="form-control" placeholder="Search by name, username, ID number or email...">
                            <button class="btn btn-primary" id="offlineSearchBtn" type="button">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <span id="offlineResultCount" class="badge bg-secondary mt-2 d-inline-block"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="offlinePaymentTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username / Email</th>
                                <th>Account Type</th>
                                <th>Faculty</th>
                                <th>Department</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="offlinePaymentBody">
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search me-2"></i>Search for a student or transfer applicant above</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Bulk Verify RRR Modal -->
<div id="bulkVerifyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Verify RRR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <p>Select a service type to verify all RRRs for that service. This will check payment status for all
                        invoices and update their status accordingly (Paid, Pending, Invalid, Unpaid).</p>
                    <form id="bulkVerifyForm" action="{{ route('admin.bulk_verify_progress') }}" method="GET">
                        <div class="form-group">
                            <label for="serviceTypeSelect">Service Type</label>
                            <select name="serviceTypeId" id="serviceTypeSelect" class="form-control" required>
                                <option value="">-- Select Service Type --</option>
                                @php
                                    $serviceTypes = DB::table('invoices')
                                        ->select('serviceTypeId', 'description')
                                        ->whereNotNull('rrr')
                                        ->distinct()
                                        ->get();
                                @endphp
                                @foreach ($serviceTypes as $serviceType)
                                    <option value="{{ $serviceType->serviceTypeId }}">
                                        {{ $serviceType->description }} ({{ $serviceType->serviceTypeId }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Process Verification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Show modal content -->
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control">
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Create Offline Payment Modal -->
<div id="createOfflinePaymentModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Offline Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createOfflineForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong id="modalUserName"></strong><br>
                        <small id="modalUserInfo" class="text-muted"></small>
                    </div>
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                        <select name="fee_type" id="modalFeeType" class="form-control" required>
                            <option value="">-- Select Fee Type --</option>
                            <optgroup label="Change of Course (Departmental)" id="cocOptions">
                                <option value="coc_voluntary">Voluntary Transfer</option>
                                <option value="coc_obligatory">Obligatory Transfer</option>
                            </optgroup>
                            <optgroup label="Inter-University Transfer" id="iutOptions">
                                <option value="iut_nigeria">Within Nigeria</option>
                                <option value="iut_abroad">From Abroad</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₦)</label>
                        <input type="text" id="modalAmountDisplay" class="form-control" readonly style="background:#f0f0f0; font-weight:bold; font-size:18px;">
                        <small class="text-muted">Amount is set automatically based on fee type from system settings</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference / Teller No <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" id="modalReference" class="form-control" required placeholder="Bank teller number or receipt reference">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Receipt (Optional)</label>
                        <input type="file" name="payment_receipt" id="modalReceipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">JPG, PNG or PDF, max 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="createPaymentBtn">
                        <i class="fas fa-check me-1"></i> Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var feeAmounts = {
        coc_voluntary: {{ \App\Http\Controllers\SystemSettingsController::get('change_of_course_fee_voluntary', 100000) }},
        coc_obligatory: {{ \App\Http\Controllers\SystemSettingsController::get('change_of_course_fee_obligatory', 50000) }},
        iut_nigeria: {{ \App\Http\Controllers\SystemSettingsController::get('inter_university_transfer_fee_nigeria', 150000) }},
        iut_abroad: {{ \App\Http\Controllers\SystemSettingsController::get('inter_university_transfer_fee_abroad', 250000) }}
    };

    function searchUsers() {
        var q = $('#offlineSearch').val().trim();
        if (q.length < 2) { alert('Enter at least 2 characters to search'); return; }
        $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</td></tr>');
        $.ajax({
            url: '/offline-payments/search',
            type: 'GET',
            data: { q: q },
            success: function(data) {
                if (data.length === 0) {
                    $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-info-circle me-2"></i>No users found</td></tr>');
                    $('#offlineResultCount').text('0 results');
                    return;
                }
                $('#offlineResultCount').text(data.length + ' result(s)');
                var html = '';
                $.each(data, function(i, row) {
                    var paidBadges = '';
                    if (row.has_paid_coc) paidBadges += '<span class="badge bg-success me-1">COC Paid</span>';
                    if (row.has_paid_iut) paidBadges += '<span class="badge bg-success me-1">IUT Paid</span>';
                    if (!paidBadges) paidBadges = '<span class="badge bg-warning text-dark">No Payment</span>';
                    var typeBadge = row.type == 'student' ? '<span class="badge bg-info">Student</span>' : '<span class="badge bg-primary">Transfer</span>';
                    html += '<tr>';
                    html += '<td>'+(i+1)+'</td>';
                    html += '<td><strong>'+row.name+'</strong></td>';
                    html += '<td>'+row.identifier+'</td>';
                    html += '<td>'+typeBadge+'</td>';
                    html += '<td>'+(row.faculty || '-')+'</td>';
                    html += '<td>'+(row.department || '-')+'</td>';
                    html += '<td>'+paidBadges+'</td>';
                    html += '<td><button class="btn btn-sm btn-success openCreatePayment" data-uid="'+row.user_id+'" data-name="'+row.name+'" data-identifier="'+row.identifier+'" data-type="'+row.type+'"><i class="fas fa-plus-circle me-1"></i>Create Payment</button></td>';
                    html += '</tr>';
                });
                $('#offlinePaymentBody').html(html);
            },
            error: function() {
                $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>Error searching</td></tr>');
            }
        });
    }

    $('#offlineSearchBtn').click(searchUsers);
    $('#offlineSearch').on('keypress', function(e) { if(e.which==13) searchUsers(); });

    $(document).on('click', '.openCreatePayment', function() {
        var btn = $(this);
        $('#modalUserName').text(btn.data('name'));
        $('#modalUserInfo').text(btn.data('type') == 'student' ? 'Student — ' + btn.data('identifier') : 'Transfer Applicant — ' + btn.data('identifier'));
        $('#modalUserId').val(btn.data('uid'));
        $('#modalFeeType').val('');
        $('#modalAmountDisplay').val('');
        $('#modalReference').val('');
        $('#modalReceipt').val('');
        // Show relevant options based on user type
        if (btn.data('type') == 'student') {
            $('#cocOptions').show();
            $('#iutOptions').hide();
        } else {
            $('#cocOptions').hide();
            $('#iutOptions').show();
        }
        var modal = new bootstrap.Modal(document.getElementById('createOfflinePaymentModal'));
        modal.show();
    });

    $('#modalFeeType').on('change', function() {
        var val = $(this).val();
        if (val && feeAmounts[val]) {
            $('#modalAmountDisplay').val('₦' + Number(feeAmounts[val]).toLocaleString('en-NG', {minimumFractionDigits:2}));
        } else {
            $('#modalAmountDisplay').val('');
        }
    });

    $('#createOfflineForm').on('submit', function(e) {
        e.preventDefault();
        if (!$('#modalFeeType').val()) { alert('Please select a fee type'); return; }
        if (!$('#modalReference').val().trim()) { alert('Please enter a payment reference'); return; }
        var formData = new FormData(this);
        $('#createPaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...');
        $.ajax({
            url: '/offline-payments/confirm',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('createOfflinePaymentModal')).hide();
                alert(res.message || 'Payment created successfully!');
                searchUsers();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message || 'Error') : 'Server error';
                alert('Error: ' + msg);
            },
            complete: function() {
                $('#createPaymentBtn').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Create Payment');
            }
        });
    });
});
</script>


<!-- Export Active Unpaid Students Modal -->
<div class="modal fade" id="exportUnpaidStudentsModal" tabindex="-1" role="dialog" aria-labelledby="exportUnpaidStudentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <div>
                    <h5 class="modal-title mb-1" id="exportUnpaidStudentsLabel">Export Active Students with Outstanding Fees</h5>
                    <small class="text-dark">Only students active in the selected session with an unpaid programme-fee balance are included.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportUnpaidStudentsForm" action="{{ route('admin.receipts.export_unpaid_students') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        A student is considered active when the session has at least one result or a session-history record for that student.
                    </div>
                    <div class="form-group mb-3">
                        <label for="exportUnpaidSession">Session <span class="text-danger">*</span></label>
                        <select class="form-control" id="exportUnpaidSession" name="session" required>
                            <option value="">Select session</option>
                            @foreach ($session as $sessionOption)
                                <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="exportUnpaidSponsor">Sponsor</label>
                        <select class="form-control" id="exportUnpaidSponsor" name="fees_type">
                            <option value="">All sponsors</option>
                            <option value="nelfund">NELFUND</option>
                            <option value="others">Others (Self-sponsored)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="exportUnpaidStudentsButton">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Paid Students Modal -->
<div class="modal fade" id="exportPaidStudentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Paid Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportPaidStudentsForm" action="/admin/receipts/export-paid-students" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="exportSession">Select Session</label>
                        <select class="form-control" id="exportSession" name="session" required>
                            <option value="">Select Session</option>
                            <option value="2028/2029">2028/2029</option>
                            <option value="2027/2028">2027/2028</option>
                            <option value="2026/2027">2026/2027</option>
                            <option value="2025/2026">2025/2026</option>
                            <option value="2024/2025" {{ ($fees_session ?? '') === '2024/2025' ? 'selected' : '' }}>2024/2025</option>
                            <option value="2023/2024">2023/2024</option>
                            <option value="2022/2023">2022/2023</option>
                            <option value="2021/2022">2021/2022</option>
                            <option value="2020/2021">2020/2021</option>
                            <option value="2019/2020">2019/2020</option>
                            <option value="2018/2019">2018/2019</option>
                            <option value="2017/2018">2017/2018</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="exportSponsor">Sponsor</label>
                        <select class="form-control" id="exportSponsor" name="fees_type">
                            <option value="">All</option>
                            <option value="nelfund">NELFUND</option>
                            <option value="others">Others (Self Sponsor)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="exportPaidStudentsForm" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>
