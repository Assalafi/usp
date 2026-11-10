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
    .fees-hero h2{font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin:0 0 6px;position:relative;z-index:1;color:#fff}.fees-hero p{margin:0;color:rgba(255,255,255,.78);position:relative;z-index:1}
    .fees-hero-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;position:relative;z-index:1}.fees-hero-actions .btn{border-radius:10px;font-weight:700;padding:.55rem .8rem}
    .fees-session-pill{display:inline-flex;align-items:center;gap:7px;margin-top:15px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:.82rem;position:relative;z-index:1}
    .fees-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px}.fees-kpi{background:#fff;border:1px solid var(--fees-border);border-radius:15px;padding:17px 18px;box-shadow:0 5px 18px rgba(24,49,80,.06);min-width:0}.fees-kpi-label{color:var(--fees-muted);font-size:.77rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.fees-kpi-value{font-size:1.25rem;color:var(--fees-ink);font-weight:800;margin-top:7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.fees-kpi-note{color:#8a97a8;font-size:.76rem;margin-top:5px}.fees-kpi.blue{border-top:4px solid #1769aa}.fees-kpi.green{border-top:4px solid #159570}.fees-kpi.amber{border-top:4px solid #de9b16}.fees-kpi.red{border-top:4px solid #d9534f}
    .fees-panel{background:#fff;border:1px solid var(--fees-border);border-radius:16px;padding:18px;box-shadow:0 5px 18px rgba(24,49,80,.05);height:100%}.fees-panel-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:15px}.fees-panel-title h5{margin:0;color:var(--fees-ink);font-size:1rem;font-weight:800}.fees-panel-title small{color:var(--fees-muted)}
    .fees-sponsor-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.fees-sponsor{border:1px solid var(--fees-border);border-radius:13px;padding:15px}.fees-sponsor.nelfund{background:linear-gradient(145deg,#f1f8ff,#fff);border-left:4px solid #1769aa}.fees-sponsor.self{background:linear-gradient(145deg,#f1fbf7,#fff);border-left:4px solid #159570}.fees-sponsor-head{display:flex;align-items:center;justify-content:space-between;gap:8px}.fees-sponsor-name{font-weight:800;color:var(--fees-ink)}.fees-sponsor-icon{width:32px;height:32px;display:grid;place-items:center;border-radius:9px;background:#e5f1fb;color:#1769aa}.fees-sponsor.self .fees-sponsor-icon{background:#e2f7ee;color:#159570}.fees-sponsor-amount{font-size:1.2rem;font-weight:800;color:var(--fees-ink);margin-top:12px}.fees-sponsor-meta{display:flex;justify-content:space-between;gap:8px;font-size:.77rem;color:var(--fees-muted);margin-top:5px}.fees-progress{height:6px;border-radius:99px;background:#edf1f6;margin-top:12px;overflow:hidden}.fees-progress span{height:100%;display:block;border-radius:inherit;background:#1769aa}.fees-sponsor.self .fees-progress span{background:#159570}
    .fees-table{width:100%;border-collapse:separate;border-spacing:0}.fees-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#748398;background:#f7f9fc;border-top:1px solid var(--fees-border);border-bottom:1px solid var(--fees-border);padding:10px 11px;white-space:nowrap}.fees-table td{padding:10px 11px;border-bottom:1px solid #eff3f7;color:#34465c;font-size:.82rem;vertical-align:middle}.fees-table tr:last-child td{border-bottom:0}.fees-table .amount{font-weight:800;white-space:nowrap;color:var(--fees-ink)}.fees-table .muted{color:#8a97a8}
    .fees-filter{background:#fff;border:1px solid var(--fees-border);border-radius:16px;box-shadow:0 5px 18px rgba(24,49,80,.05);padding:18px;margin-top:16px}.fees-filter .form-label{font-size:.75rem;font-weight:700;color:#52657b;margin-bottom:5px}.fees-filter .form-control,.fees-filter .form-select{border-color:#dce5ef;border-radius:9px;min-height:40px;font-size:.84rem}.fees-filter .btn{border-radius:9px;min-height:40px;font-weight:700}.fees-filter-heading{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}.fees-filter-heading h5{margin:0;color:var(--fees-ink);font-size:1rem;font-weight:800}.fees-filter-heading small{color:var(--fees-muted)}
    .fees-results-info{font-size:.82rem}.fees-pagination-wrap .pagination{margin:0;gap:4px}.fees-pagination-wrap .page-item .page-link{border:1px solid #dfe7f1;border-radius:8px;color:#36516d;font-size:.8rem;font-weight:700;min-width:34px;text-align:center;padding:.45rem .65rem}.fees-pagination-wrap .page-item.active .page-link{background:#1769aa;border-color:#1769aa;color:#fff}.fees-pagination-wrap .page-item.disabled .page-link{color:#a5b1bf;background:#f7f9fc}@media(max-width:575px){.fees-pagination-row>[class*=col-]{width:100%}.fees-pagination-wrap{justify-content:flex-start!important;margin-top:10px;overflow-x:auto;padding-bottom:3px}.fees-pagination-wrap .pagination{flex-wrap:nowrap}.fees-results-info{font-size:.75rem}}
    .fees-section{margin-top:16px}.fees-section .card{border:1px solid var(--fees-border);border-radius:16px;box-shadow:0 5px 18px rgba(24,49,80,.05);overflow:hidden}.fees-section .card-block{padding:0}.fees-section .table-responsive{border-radius:inherit}.fees-section .table{margin-bottom:0}.fees-section .table thead th{background:#f7f9fc;color:#667890;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--fees-border)}
    .fees-school-overview{margin-top:16px;background:#fff;border:1px solid var(--fees-border);border-radius:18px;box-shadow:0 8px 24px rgba(24,49,80,.06);overflow:hidden}
    .fees-school-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:20px 22px;border-bottom:1px solid var(--fees-border);background:linear-gradient(180deg,#fbfdff,#fff)}
    .fees-school-header h4{margin:0;color:var(--fees-ink);font-size:1.08rem;font-weight:800}.fees-school-header p{margin:5px 0 0;color:var(--fees-muted);font-size:.82rem}
    .fees-school-refresh{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border-radius:999px;background:#f2f7fc;color:#54708d;font-size:.75rem;font-weight:700;white-space:nowrap}.fees-school-refresh.is-ready{background:#eaf8f1;color:#15754f}.fees-school-refresh.is-error{background:#fff2f1;color:#a13d38}
    .fees-school-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:14px 22px;border-bottom:1px solid var(--fees-border);background:#fcfdff}
    .fees-school-summary-item{display:flex;align-items:center;gap:10px;min-width:0}.fees-school-summary-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#eaf3fb;color:#1769aa;flex:0 0 auto}.fees-school-summary-item:nth-child(2) .fees-school-summary-icon{background:#eaf8f1;color:#159570}.fees-school-summary-item:nth-child(3) .fees-school-summary-icon{background:#fff6df;color:#b17a08}.fees-school-summary strong{display:block;color:var(--fees-ink);font-size:1rem;line-height:1.2}.fees-school-summary small{display:block;color:var(--fees-muted);font-size:.72rem;margin-top:2px}
    .fees-school-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;padding:18px 22px}
    .fees-school-card{min-width:0;border:1px solid var(--fees-border);border-radius:14px;padding:15px;background:#fff;position:relative;overflow:hidden}.fees-school-card:before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:#1769aa}.fees-school-card[data-school-key=pg]:before{background:#7c5ac7}.fees-school-card[data-school-key=sbrs]:before{background:#159570}.fees-school-card[data-school-key=institute]:before{background:#e39a17}.fees-school-card.is-unavailable{background:#fbfcfe}.fees-school-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}.fees-school-name{font-weight:800;color:var(--fees-ink);font-size:.92rem}.fees-school-code{display:inline-flex;margin-top:3px;padding:2px 7px;border-radius:999px;background:#f0f4f8;color:#667890;font-size:.66rem;font-weight:800;letter-spacing:.05em}.fees-school-status{font-size:.66rem;font-weight:800;white-space:nowrap;color:#15845a}.fees-school-status.is-unavailable{color:#9a6d17}.fees-school-amount{font-size:1.25rem;color:var(--fees-ink);font-weight:800;margin-top:18px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.fees-school-meta{display:flex;justify-content:space-between;gap:8px;color:var(--fees-muted);font-size:.75rem;margin-top:5px}.fees-school-note{min-height:31px;color:#8a97a8;font-size:.7rem;line-height:1.4;margin-top:10px}.fees-school-services{margin-top:10px;padding-top:10px;border-top:1px solid #edf1f6}.fees-school-service-line{display:flex;justify-content:space-between;gap:8px;color:#62748a;font-size:.7rem}.fees-school-service-line+.fees-school-service-line{margin-top:4px}.fees-school-service-line span:first-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.fees-school-service-line strong{color:var(--fees-ink);white-space:nowrap}
    .fees-school-service-panel{padding:0 22px 20px}.fees-school-service-panel .fees-panel-title{margin:0 0 10px}.fees-school-service-panel .table-responsive{border:1px solid var(--fees-border);border-radius:12px}.fees-school-service-panel .fees-table th{border-top:0}.fees-school-service-empty{color:#8a97a8;font-size:.8rem;text-align:center;padding:18px}
    .fees-records-workspace{margin-top:16px;background:#fff;border:1px solid var(--fees-border);border-radius:18px;box-shadow:0 8px 24px rgba(24,49,80,.06);overflow:hidden}.fees-records-heading{padding:20px 22px 12px}.fees-records-heading h4{margin:0;color:var(--fees-ink);font-size:1.08rem;font-weight:800}.fees-records-heading p{margin:5px 0 0;color:var(--fees-muted);font-size:.82rem}
    .fees-school-tabs{display:flex;gap:7px;overflow-x:auto;padding:0 22px;border-bottom:1px solid var(--fees-border);scrollbar-width:thin}.fees-school-tab{border:0;background:transparent;border-bottom:3px solid transparent;color:#667890;font-size:.8rem;font-weight:800;padding:12px 13px;white-space:nowrap;cursor:pointer}.fees-school-tab:hover{color:#1769aa}.fees-school-tab.active{color:#1769aa;border-bottom-color:#1769aa;background:#f8fbff}.fees-school-pane{padding:18px 22px 22px}.fees-record-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px}.fees-record-kpi{border:1px solid var(--fees-border);border-radius:12px;padding:13px 14px;background:#fbfdff}.fees-record-kpi small{display:block;color:var(--fees-muted);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.fees-record-kpi strong{display:block;color:var(--fees-ink);font-size:1.08rem;margin-top:5px}.fees-record-filters{border:1px solid var(--fees-border);background:#f9fbfd;border-radius:12px;padding:12px;margin-bottom:14px}.fees-record-filters .form-label{font-size:.7rem;font-weight:800;color:#52657b;margin-bottom:4px}.fees-record-filters .form-control,.fees-record-filters .form-select{height:38px;border-color:#dce5ef;border-radius:8px;font-size:.78rem}.fees-record-filters .btn{height:38px;border-radius:8px;font-size:.78rem;font-weight:800}.fees-record-table{width:100%;border-collapse:separate;border-spacing:0}.fees-record-table th{padding:9px 10px;background:#f5f8fc;color:#708197;font-size:.68rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--fees-border);white-space:nowrap}.fees-record-table td{padding:10px;border-bottom:1px solid #edf1f6;color:#34465c;font-size:.78rem;vertical-align:middle}.fees-record-table .record-name{font-weight:800;color:var(--fees-ink)}.fees-record-table .record-muted{color:#8794a5;font-size:.7rem}.fees-record-table .record-amount{font-weight:800;color:var(--fees-ink);white-space:nowrap}.fees-record-status{display:inline-flex;align-items:center;padding:3px 7px;border-radius:999px;background:#eaf8f1;color:#15754f;font-size:.65rem;font-weight:800}.fees-record-empty{padding:24px!important;text-align:center;color:#8a97a8}.fees-record-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;padding-top:11px;color:#75859a;font-size:.72rem}.fees-record-loading{color:#1769aa}.fees-legacy-ug{margin-top:16px}.fees-legacy-ug>summary{cursor:pointer;list-style:none;padding:13px 16px;border:1px solid var(--fees-border);border-radius:12px;background:#fff;color:var(--fees-ink);font-size:.82rem;font-weight:800}.fees-legacy-ug>summary::-webkit-details-marker{display:none}.fees-legacy-ug[open]>summary{border-radius:12px 12px 0 0;border-bottom-color:transparent}.fees-legacy-ug-content{padding-top:0}
    @media(max-width:991px){.fees-school-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:575px){.fees-school-header{padding:16px;display:block}.fees-school-refresh{margin-top:12px}.fees-school-summary{grid-template-columns:1fr;padding:12px 16px}.fees-school-grid{grid-template-columns:1fr;padding:14px 16px}.fees-school-service-panel{padding:0 16px 16px}.fees-school-card{padding:14px}.fees-school-amount{font-size:1.16rem}.fees-records-heading{padding:16px 16px 10px}.fees-school-tabs{padding:0 16px}.fees-school-tab{padding:11px 10px;font-size:.74rem}.fees-school-pane{padding:14px 16px 16px}.fees-record-kpis{grid-template-columns:1fr 1fr}.fees-record-kpi:last-child{grid-column:1 / -1}.fees-record-filters .row>[class*=col-]{margin-bottom:8px}.fees-record-table{min-width:650px}.fees-record-foot{align-items:flex-start;flex-direction:column}}
    @media(max-width:991px){.fees-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.fees-hero-actions{justify-content:flex-start;margin-top:16px}.fees-panel{margin-bottom:14px}}
    @media(max-width:575px){.fees-dashboard{margin:0 -2px 18px}.fees-hero{padding:20px 17px;border-radius:15px}.fees-hero h2{font-size:1.22rem}.fees-hero-actions .btn{font-size:.75rem;padding:.48rem .62rem}.fees-kpis{grid-template-columns:1fr 1fr;gap:9px}.fees-kpi{padding:13px 12px;border-radius:12px}.fees-kpi-value{font-size:1rem}.fees-kpi-note{font-size:.68rem}.fees-sponsor-grid{grid-template-columns:1fr}.fees-panel{padding:14px;border-radius:13px}.fees-table th,.fees-table td{padding:8px 7px}.fees-filter{padding:14px;border-radius:13px}.fees-filter-heading{align-items:flex-start;gap:10px}.fees-filter-heading small{font-size:.7rem}.fees-filter .row>[class*=col-]{margin-bottom:9px}}
</style>

@php
    $isAdmin = session('accType') === 'Admin';
    $schoolFeeTotals = $school_fee_totals ?? [];
    $sponsorSummary = $fee_sponsor_summary ?? [];
    $unpaidSummary = $fee_unpaid_summary ?? [];
    $levelSummary = $fee_level_summary ?? [];
    $nelfund = $sponsorSummary['nelfund'] ?? ['students' => 0, 'required_amount' => 0, 'amount_paid' => 0, 'outstanding_amount' => 0, 'fully_paid' => 0];
    $selfSponsor = $sponsorSummary['self'] ?? ['students' => 0, 'required_amount' => 0, 'amount_paid' => 0, 'outstanding_amount' => 0, 'fully_paid' => 0];
    $nelfundRate = ($nelfund['required_amount'] ?? 0) > 0 ? min(100, (($nelfund['amount_paid'] ?? 0) / $nelfund['required_amount']) * 100) : 0;
    $selfRate = ($selfSponsor['required_amount'] ?? 0) > 0 ? min(100, (($selfSponsor['amount_paid'] ?? 0) / $selfSponsor['required_amount']) * 100) : 0;
    $hostelPinSummary = $hostel_pin_summary ?? [];
    $paymentRecords = (int) collect($payment_summary ?? [])->sum('count') + (int) ($hostelPinSummary['count'] ?? 0);
    $exportLevels = DB::table('students')->whereNotNull('level')->where('level', '!=', '')->select('level')->distinct()->orderBy('level')->pluck('level');
@endphp

<div class="fees-dashboard">
    <div class="fees-hero">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h2><i class="fas fa-wallet me-2"></i>Fees &amp; payment control</h2>
                <p>Review programme-fee collections, NELFUND support, self-sponsored payments and active outstanding balances in one place.</p>
                <span class="fees-session-pill"><i class="far fa-calendar-alt"></i> Session {{ $fees_session ?? '—' }}</span>
            </div>
            @if ($isAdmin)
            <div class="col-lg-5">
                <div class="fees-hero-actions">
                    <a href="{{ route('admin.make-payment') }}" class="btn btn-success"><i class="fas fa-money-check-dollar me-1"></i> Make payment</a>
                    <a href="/admin/receipts" class="btn btn-light"><i class="fas fa-file-pdf me-1"></i> Receipts</a>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#exportPaidStudentsModal"><i class="fas fa-file-excel me-1"></i> Paid export</button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exportUnpaidStudentsModal"><i class="fas fa-user-clock me-1"></i> Unpaid export</button>
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#bulkVerifyModal"><i class="fas fa-check-circle me-1"></i> Verify RRR</button>
                     @if (in_array(strtoupper((string) session('username')), ['SP11913', 'SU'], true))
                         <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#importPayment"><i class="fas fa-upload me-1"></i> Upload payment</button>
                     @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="fees-kpis">
        <div class="fees-kpi blue"><div class="fees-kpi-label">Total collected</div><div class="fees-kpi-value">N{{ number_format((float) ($payment_summary_total ?? 0), 2) }}</div><div class="fees-kpi-note">All paid services and validated hostel PINs</div></div>
        <div class="fees-kpi red"><div class="fees-kpi-label">Active outstanding</div><div class="fees-kpi-value">N{{ number_format((float) ($unpaidSummary['outstanding_amount'] ?? 0), 2) }}</div><div class="fees-kpi-note">{{ number_format((int) ($unpaidSummary['students'] ?? 0)) }} active student{{ (($unpaidSummary['students'] ?? 0) == 1) ? '' : 's' }} still owing</div></div>
        <div class="fees-kpi green"><div class="fees-kpi-label">Programme fees paid</div><div class="fees-kpi-value">N{{ number_format((float) ($schoolFeeTotals['amount_paid'] ?? 0), 2) }}</div><div class="fees-kpi-note">Validated school-fee collection</div></div>
        <div class="fees-kpi amber"><div class="fees-kpi-label">Paid records + hostel PINs</div><div class="fees-kpi-value">{{ number_format($paymentRecords) }}</div><div class="fees-kpi-note">Invoices plus validated hostel PIN records</div></div>
    </div>

    <section class="fees-school-overview" id="feesSchoolOverview"
        data-url="{{ route('fees-due.school-summary') }}"
        data-session="{{ $fees_session ?? '' }}"
        aria-labelledby="feesSchoolOverviewTitle">
        <div class="fees-school-header">
            <div>
                <h4 id="feesSchoolOverviewTitle"><i class="fas fa-building-columns me-2 text-primary"></i>Collections across schools</h4>
                <p>One clear view of successful payments received by each school for the selected session.</p>
            </div>
            <span class="fees-school-refresh" id="feesSchoolRefresh"><i class="fas fa-circle-notch fa-spin"></i> Loading collections</span>
        </div>
        <div class="fees-school-summary" aria-label="School collection totals">
            <div class="fees-school-summary-item"><span class="fees-school-summary-icon"><i class="fas fa-school"></i></span><span><strong id="feesSchoolCount">—</strong><small>Schools reporting</small></span></div>
            <div class="fees-school-summary-item"><span class="fees-school-summary-icon"><i class="fas fa-money-bill-wave"></i></span><span><strong id="feesSchoolAmount">—</strong><small>Total collected</small></span></div>
            <div class="fees-school-summary-item"><span class="fees-school-summary-icon"><i class="fas fa-receipt"></i></span><span><strong id="feesSchoolPayments">—</strong><small>Successful payments</small></span></div>
        </div>
        <div class="fees-school-grid" id="feesSchoolCards">
            @foreach ([['key' => 'ug', 'label' => 'Undergraduate', 'short' => 'UG'], ['key' => 'pg', 'label' => 'Postgraduate', 'short' => 'PG'], ['key' => 'sbrs', 'label' => 'School of Basic & Remedial Studies', 'short' => 'SBRS'], ['key' => 'institute', 'label' => 'Institute', 'short' => 'Institute']] as $school)
                <article class="fees-school-card" data-school-key="{{ $school['key'] }}">
                    <div class="fees-school-card-head"><div><div class="fees-school-name">{{ $school['label'] }}</div><span class="fees-school-code">{{ $school['short'] }}</span></div><span class="fees-school-status"><i class="fas fa-circle-notch fa-spin"></i></span></div>
                    <div class="fees-school-amount">Loading…</div>
                    <div class="fees-school-meta"><span>— payments</span><span>—</span></div>
                    <div class="fees-school-note">Checking the school payment records.</div>
                </article>
            @endforeach
        </div>
        <div class="fees-school-service-panel">
            <div class="fees-panel-title"><div><h5>Paid services by school</h5><small>Service descriptions are grouped under the school that received the payment.</small></div><i class="fas fa-layer-group text-primary"></i></div>
            <div class="table-responsive">
                <table class="fees-table mb-0" id="feesSchoolServices">
                    <thead><tr><th>School</th><th>Service</th><th>Payments</th><th class="text-end">Amount</th></tr></thead>
                    <tbody><tr><td colspan="4" class="fees-school-service-empty">Loading service details…</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="fees-records-workspace" id="feesRecordsWorkspace"
        data-url="{{ route('fees-due.school-records') }}"
        data-session="{{ $fees_session ?? '' }}"
        aria-labelledby="feesRecordsTitle">
        <div class="fees-records-heading">
            <h4 id="feesRecordsTitle"><i class="fas fa-table-list me-2 text-primary"></i>Payment records by school</h4>
            <p>Choose a school to review its paid records. Filters update the list without reloading the page.</p>
        </div>
        <nav class="fees-school-tabs" aria-label="School payment records">
            <button type="button" class="fees-school-tab active" data-school-tab="ug"><i class="fas fa-graduation-cap me-1"></i> Undergraduate</button>
            <button type="button" class="fees-school-tab" data-school-tab="pg"><i class="fas fa-user-graduate me-1"></i> Postgraduate</button>
            <button type="button" class="fees-school-tab" data-school-tab="sbrs"><i class="fas fa-book-open me-1"></i> SBRS</button>
            <button type="button" class="fees-school-tab" data-school-tab="institute"><i class="fas fa-building me-1"></i> Institute</button>
        </nav>
        <div class="fees-school-pane" id="feesRecordsPane">
            <div class="fees-record-kpis">
                <div class="fees-record-kpi"><small>Successful payments</small><strong id="feesRecordsCount">—</strong></div>
                <div class="fees-record-kpi"><small>Collected</small><strong id="feesRecordsAmount">—</strong></div>
                <div class="fees-record-kpi"><small>View</small><strong id="feesRecordsStatus">Loading…</strong></div>
            </div>
            <form class="fees-record-filters" id="feesRecordsFilter" autocomplete="off">
                <div class="row gx-2 align-items-end">
                    <div class="col-lg-4 col-md-6"><label class="form-label" for="feesRecordsSearch">Search records</label><input class="form-control" id="feesRecordsSearch" type="search" placeholder="Name, student ID, RRR or description"></div>
                    <div class="col-lg-3 col-md-6"><label class="form-label" for="feesRecordsService">Payment service</label><select class="form-select" id="feesRecordsService"><option value="">All paid services</option></select></div>
                    <div class="col-lg-2 col-md-6"><label class="form-label" for="feesRecordsFrom">From</label><input class="form-control" id="feesRecordsFrom" type="date"></div>
                    <div class="col-lg-2 col-md-6"><label class="form-label" for="feesRecordsTo">To</label><input class="form-control" id="feesRecordsTo" type="date"></div>
                    <div class="col-lg-1 col-md-6"><button type="submit" class="btn btn-primary w-100" title="Apply filters"><i class="fas fa-search"></i><span class="d-lg-none ms-1">Apply</span></button></div>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mt-2"><small class="text-muted">Session: {{ $fees_session ?? 'Current session' }}</small><button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="feesRecordsReset">Clear filters</button></div>
            </form>
            <div class="table-responsive">
                <table class="fees-record-table" id="feesRecordsTable">
                    <thead><tr><th>#</th><th>Payer / student</th><th>Reference</th><th>Service</th><th>Status</th><th class="text-end">Amount</th><th>Date</th></tr></thead>
                    <tbody><tr><td colspan="7" class="fees-record-empty"><span class="fees-record-loading"><i class="fas fa-circle-notch fa-spin me-1"></i> Loading payment records…</span></td></tr></tbody>
                </table>
            </div>
            <div class="fees-record-foot"><span id="feesRecordsFootnote">Paid records only.</span><span id="feesRecordsUpdated"></span></div>
        </div>
    </section>

    <details class="fees-legacy-ug">
        <summary><i class="fas fa-sliders me-2 text-primary"></i> Undergraduate reconciliation details</summary>
        <div class="fees-legacy-ug-content">
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
                <div class="fees-panel-title"><div><h5>Paid payments by description</h5><small>Every paid invoice and validated hostel PIN</small></div><i class="fas fa-list-alt text-primary"></i></div>
                <div class="table-responsive"><table class="fees-table"><thead><tr><th>Description</th><th>Records / students</th><th class="text-end">Amount</th></tr></thead><tbody>
                    @forelse (($payment_summary ?? collect()) as $summary)
                        <tr><td class="text-wrap" style="min-width:180px">{{ $summary['description'] }}</td><td>{{ number_format((int) ($summary['count'] ?? 0)) }}</td><td class="amount text-end">N{{ number_format((float) $summary['amount'], 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center muted py-4">No paid payments found for this session.</td></tr>
                    @endforelse
                    @if (($hostelPinSummary['count'] ?? 0) > 0)
                        <tr><td><span class="badge bg-light text-dark">HOSTEL PIN</span><small class="d-block text-muted">Validated PIN records</small></td><td>{{ number_format((int) ($hostelPinSummary['count'] ?? 0)) }}</td><td class="amount text-end">N{{ number_format((float) ($hostelPinSummary['amount'] ?? 0), 2) }}</td></tr>
                    @endif
                    <tr><td><strong>Total collected</strong></td><td><strong>{{ number_format($paymentRecords) }}</strong></td><td class="amount text-end"><strong>N{{ number_format((float) ($payment_summary_total ?? 0), 2) }}</strong></td></tr>
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
                        @if ($isAdmin)
                            <div class="col-12 mt-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search payments</button></div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
        </div>
    </details>
</div>

@if (in_array(strtoupper((string) session('username')), ['SP11913', 'SU'], true))
    <div id="importPayment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="importPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title mb-1" id="importPaymentLabel">Upload payment records</h5>
                        <small>Import payment records into the collection.</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('/upload-payment') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadService">Service</label>
                            <select class="form-select" name="service" id="paymentUploadService" required>
                                <option value="">Select service</option>
                                <option value="school fees">School Fees</option>
                                <option value="hostel fees">Hostel Fees</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadType">Payment type</label>
                            <select class="form-select" name="upload_type" id="paymentUploadType" required>
                                <option value="">Select payment type</option>
                                <option value="full">Full Payment</option>
                                <option value="part">Part Payment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadSponsor">Sponsor</label>
                            <select class="form-select" name="sponsor" id="paymentUploadSponsor" required>
                                <option value="">Select sponsor</option>
                                <option value="self sponsor">Self Sponsor</option>
                                <option value="nelfund">NELFUND</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadSession">Session</label>
                            <select class="form-select" name="session" id="paymentUploadSession" required>
                                <option value="">Select session</option>
                                @foreach (($session ?? collect()) as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadFile">Excel file</label>
                            <input type="file" name="file" id="paymentUploadFile" accept=".xlsx,.xls" class="form-control" required>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">
                            Confirm the service, payment type, sponsor and session before uploading. The imported records will appear in this collection.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload records</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
<details class="fees-legacy-ug">
    <summary><i class="fas fa-user-shield me-2 text-primary"></i> Undergraduate payment administration</summary>
    <div class="fees-legacy-ug-content">
            <div class="col-sm-12 fees-section">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div><h5 class="mb-1"><i class="fas fa-receipt me-2 text-primary"></i>Payment transactions</h5><small class="text-muted">Showing up to 100 records for the selected session and filters.</small></div>
                        <span class="badge bg-light text-dark"><i class="fas fa-database me-1"></i>Live records</span>
                    </div>
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover {{ $isAdmin ? '' : 'fees-readonly-table' }}"
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
                                        @if ($isAdmin)<th>{{ 'ACTION' }}</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>N{{ number_format($row->amount, 2) }}</td>
                                            <td>{{ $row->rrr }}</td>
                                            <td>{{ $row->description }}</td>
                                            <td>{{ $row->phone }}</td>
                                            <td>{{ $row->status }}</td>
                                            <td>{{ date('d-m-y', strtotime($row->updated_at)) }}</td>
                                            @if ($isAdmin)
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
                                            @endif
                                        </tr>

                                        @if ($isAdmin)
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
                    <div class="form-group mt-3">
                        <label for="base_amount{{ $row->id }}">Base amount</label>
                        <div class="input-group">
                            <span class="input-group-text">&#8358;</span>
                            <input type="number" min="0" step="0.01" class="form-control"
                                id="base_amount{{ $row->id }}" name="base_amount"
                                value="{{ old('base_amount', $row->base_amount ?? $row->amount) }}" required>
                        </div>
                        <small class="form-text text-muted">Enter the main payment amount. Any existing service charge remains unchanged and the total is recalculated.</small>
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
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center text-muted py-4">
                                                <i class="fas fa-filter me-1"></i>Apply a filter above to load payment transactions.
                                            </td>
                                        </tr>
                                    @endforelse
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

@if ($isAdmin)
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
@endif

    </div>
</details>

<script>
(function () {
    const root = document.getElementById('feesRecordsWorkspace');
    if (!root || typeof window.fetch !== 'function') return;
    const url = root.dataset.url || '';
    const session = root.dataset.session || '';
    const tabs = Array.from(document.querySelectorAll('[data-school-tab]'));
    const filterForm = document.getElementById('feesRecordsFilter');
    const search = document.getElementById('feesRecordsSearch');
    const service = document.getElementById('feesRecordsService');
    const from = document.getElementById('feesRecordsFrom');
    const to = document.getElementById('feesRecordsTo');
    const reset = document.getElementById('feesRecordsReset');
    const tableBody = document.querySelector('#feesRecordsTable tbody');
    const countEl = document.getElementById('feesRecordsCount');
    const amountEl = document.getElementById('feesRecordsAmount');
    const statusEl = document.getElementById('feesRecordsStatus');
    const footnote = document.getElementById('feesRecordsFootnote');
    const updated = document.getElementById('feesRecordsUpdated');
    const number = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const integer = new Intl.NumberFormat('en-NG');
    const filtersBySchool = {};
    let school = 'ug';
    let requestToken = 0;
    let timer;

    const naira = value => '₦' + number.format(Number(value || 0));
    const getFilters = () => filtersBySchool[school] || (filtersBySchool[school] = { q: '', service: '', date_from: '', date_to: '' });
    const setLoading = text => {
        statusEl.textContent = text || 'Loading…';
        tableBody.innerHTML = '<tr><td colspan="7" class="fees-record-empty"><span class="fees-record-loading"><i class="fas fa-circle-notch fa-spin me-1"></i> Loading payment records…</span></td></tr>';
    };
    const setFormValues = () => {
        const values = getFilters();
        search.value = values.q || '';
        from.value = values.date_from || '';
        to.value = values.date_to || '';
        service.value = values.service || '';
    };
    const cell = (value, className) => {
        const node = document.createElement('td');
        if (className) node.className = className;
        node.textContent = value == null || value === '' ? '—' : value;
        return node;
    };
    const formatDate = value => {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    };
    const renderServices = services => {
        const current = getFilters().service || '';
        service.innerHTML = '<option value="">All paid services</option>';
        (services || []).forEach(item => {
            const option = document.createElement('option');
            option.value = item.label || '';
            option.textContent = (item.label || 'Payment') + ' · ' + integer.format(Number(item.payments || 0));
            service.appendChild(option);
        });
        service.value = current;
    };
    const render = data => {
        const filters = getFilters();
        const summary = data.summary || {};
        countEl.textContent = integer.format(Number(summary.payments || 0));
        amountEl.textContent = naira(summary.amount);
        statusEl.textContent = data.status === 'ok' ? 'Paid records' : 'Temporarily unavailable';
        renderServices(data.services || []);
        tableBody.innerHTML = '';
        if (data.status !== 'ok') {
            const row = document.createElement('tr');
            const empty = document.createElement('td');
            empty.colSpan = 7;
            empty.className = 'fees-record-empty';
            empty.innerHTML = '<i class="fas fa-triangle-exclamation text-warning me-1"></i> ' + (data.error || 'This school payment source could not be reached.');
            row.appendChild(empty);
            tableBody.appendChild(row);
            footnote.textContent = 'Try again later; other school tabs remain available.';
        } else if (!Array.isArray(data.rows) || data.rows.length === 0) {
            const row = document.createElement('tr');
            const empty = document.createElement('td');
            empty.colSpan = 7;
            empty.className = 'fees-record-empty';
            empty.textContent = 'No paid records match the selected filters.';
            row.appendChild(empty);
            tableBody.appendChild(row);
            footnote.textContent = 'Paid records only.';
        } else {
            data.rows.forEach((item, index) => {
                const row = document.createElement('tr');
                row.appendChild(cell(index + 1));
                const payer = document.createElement('td');
                const name = document.createElement('div');
                name.className = 'record-name';
                name.textContent = item.name || 'Payment record';
                const id = document.createElement('div');
                id.className = 'record-muted';
                id.textContent = item.student_id || '';
                payer.appendChild(name);
                if (id.textContent) payer.appendChild(id);
                row.appendChild(payer);
                row.appendChild(cell(item.reference || '—'));
                row.appendChild(cell(item.description || 'Payment'));
                const status = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = 'fees-record-status';
                badge.textContent = item.status || 'Paid';
                status.appendChild(badge);
                row.appendChild(status);
                row.appendChild(cell(naira(item.amount), 'record-amount text-end'));
                row.appendChild(cell(formatDate(item.created_at), 'record-muted'));
                tableBody.appendChild(row);
            });
            footnote.textContent = data.has_more ? 'Showing the latest records. Use filters to narrow the list.' : 'Paid records only.';
        }
        updated.textContent = data.status === 'ok' ? 'Updated just now' : '';
        Object.assign(filters, { service: service.value || '' });
    };
    async function load() {
        const token = ++requestToken;
        const values = getFilters();
        setLoading('Loading…');
        const params = new URLSearchParams({ school, session });
        Object.keys(values).forEach(key => { if (values[key]) params.set(key, values[key]); });
        try {
            const response = await fetch(url + '?' + params.toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) throw new Error('Unable to load records.');
            const data = await response.json();
            if (token !== requestToken) return;
            render(data);
        } catch (error) {
            if (token !== requestToken) return;
            render({ status: 'unavailable', error: 'Payment records could not be loaded right now.', summary: { payments: 0, amount: 0 }, services: [], rows: [] });
        }
    }
    tabs.forEach(button => button.addEventListener('click', () => {
        school = button.dataset.schoolTab || 'ug';
        tabs.forEach(item => item.classList.toggle('active', item === button));
        setFormValues();
        load();
    }));
    filterForm.addEventListener('submit', event => {
        event.preventDefault();
        filtersBySchool[school] = { q: search.value.trim(), service: service.value, date_from: from.value, date_to: to.value };
        load();
    });
    reset.addEventListener('click', () => {
        filtersBySchool[school] = { q: '', service: '', date_from: '', date_to: '' };
        setFormValues();
        load();
    });
    setFormValues();
    load();
    timer = window.setInterval(() => { if (!document.hidden) load(); }, 60000);
    window.addEventListener('beforeunload', () => window.clearInterval(timer));
})();
</script>

<script>
(function () {
    const root = document.getElementById('feesSchoolOverview');
    if (!root || typeof window.fetch !== 'function') return;

    const cards = document.getElementById('feesSchoolCards');
    const servicesBody = document.querySelector('#feesSchoolServices tbody');
    const refreshBadge = document.getElementById('feesSchoolRefresh');
    const countEl = document.getElementById('feesSchoolCount');
    const amountEl = document.getElementById('feesSchoolAmount');
    const paymentsEl = document.getElementById('feesSchoolPayments');
    const url = root.dataset.url || '';
    const session = root.dataset.session || '';
    const number = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const integer = new Intl.NumberFormat('en-NG');

    const naira = value => '₦' + number.format(Number(value || 0));
    const setRefresh = (text, state, icon) => {
        refreshBadge.className = 'fees-school-refresh' + (state ? ' is-' + state : '');
        refreshBadge.innerHTML = '<i class="fas ' + icon + '"></i> ' + text;
    };
    const statusText = status => status === 'ok' ? 'Available' : 'Temporarily unavailable';

    function render(data) {
        const schools = Array.isArray(data.schools) ? data.schools : [];
        const available = schools.filter(school => school.status === 'ok');
        countEl.textContent = integer.format(available.length);
        amountEl.textContent = naira(data.totals && data.totals.amount);
        paymentsEl.textContent = integer.format(Number(data.totals && data.totals.payments || 0));

        schools.forEach(school => {
            const card = cards.querySelector('[data-school-key="' + school.key + '"]');
            if (!card) return;
            const status = card.querySelector('.fees-school-status');
            const amount = card.querySelector('.fees-school-amount');
            const meta = card.querySelector('.fees-school-meta');
            const note = card.querySelector('.fees-school-note');
            const serviceList = document.createElement('div');
            serviceList.className = 'fees-school-services';

            status.className = 'fees-school-status' + (school.status === 'ok' ? '' : ' is-unavailable');
            status.innerHTML = '<i class="fas ' + (school.status === 'ok' ? 'fa-circle-check' : 'fa-triangle-exclamation') + '"></i> ' + statusText(school.status);
            amount.textContent = school.status === 'ok' ? naira(school.amount) : 'Not available';
            meta.innerHTML = '<span>' + (school.status === 'ok' ? integer.format(Number(school.payments || 0)) + ' payments' : 'No figures') + '</span><span>' + (school.status === 'ok' ? 'Paid' : 'Check later') + '</span>';
            note.textContent = school.status === 'ok'
                ? ((school.services || []).length ? 'Successful payments grouped by service below.' : 'No paid records found for this session.')
                : (school.error || 'This school could not return its payment summary right now.');
            card.classList.toggle('is-unavailable', school.status !== 'ok');

            (school.services || []).slice(0, 3).forEach(service => {
                const line = document.createElement('div');
                line.className = 'fees-school-service-line';
                const label = document.createElement('span');
                label.textContent = service.label || 'Payment';
                const value = document.createElement('strong');
                value.textContent = naira(service.amount);
                line.appendChild(label);
                line.appendChild(value);
                serviceList.appendChild(line);
            });
            const existing = card.querySelector('.fees-school-services');
            if (existing) existing.remove();
            if (serviceList.children.length) card.appendChild(serviceList);
        });

        servicesBody.innerHTML = '';
        const rows = [];
        schools.forEach(school => {
            if (school.status !== 'ok') return;
            (school.services || []).forEach(service => rows.push({
                school: school.label,
                label: service.label || 'Payment',
                payments: Number(service.payments || 0),
                amount: Number(service.amount || 0)
            }));
        });
        rows.sort((a, b) => b.amount - a.amount);
        if (!rows.length) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="4" class="fees-school-service-empty">No paid service records were returned for this session.</td>';
            servicesBody.appendChild(row);
            return;
        }
        rows.forEach(item => {
            const row = document.createElement('tr');
            [item.school, item.label, integer.format(item.payments)].forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.appendChild(cell);
            });
            const amountCell = document.createElement('td');
            amountCell.className = 'amount text-end';
            amountCell.textContent = naira(item.amount);
            row.appendChild(amountCell);
            servicesBody.appendChild(row);
        });
    }

    async function load() {
        setRefresh('Refreshing collections', '', 'fa-circle-notch fa-spin');
        try {
            const response = await fetch(url + '?session=' + encodeURIComponent(session), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (!response.ok) throw new Error('The school collection summary is temporarily unavailable.');
            const data = await response.json();
            if (!data || data.success !== true) throw new Error('The school collection summary is temporarily unavailable.');
            render(data);
            setRefresh('Updated just now', 'ready', 'fa-circle-check');
        } catch (error) {
            setRefresh('Could not refresh now', 'error', 'fa-triangle-exclamation');
            countEl.textContent = '—';
            amountEl.textContent = '—';
            paymentsEl.textContent = '—';
            servicesBody.innerHTML = '<tr><td colspan="4" class="fees-school-service-empty">The other school summaries could not be loaded. The UG records above remain available.</td></tr>';
        }
    }

    load();
    window.setInterval(load, 60000);
})();
</script>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

@if ($isAdmin)
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
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <div>
                    <h5 class="modal-title mb-1" id="exportUnpaidStudentsLabel">Export students with outstanding fees</h5>
                    <small class="text-dark">Use any combination of filters. The workbook includes the matching students and a totals summary.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportUnpaidStudentsForm" action="{{ route('admin.receipts.export_unpaid_students') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        Unpaid records include active students with a result or session-history record in the selected session.
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="exportUnpaidSession">Session <span class="text-danger">*</span></label>
                            <select class="form-control" id="exportUnpaidSession" name="session" required>
                                <option value="">Select session</option>
                                @foreach ($session as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="exportUnpaidSponsor">Sponsor</label>
                            <select class="form-control" id="exportUnpaidSponsor" name="fees_type">
                                <option value="">All sponsors</option>
                                <option value="nelfund">NELFUND</option>
                                <option value="others">Others (Self-sponsored)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="facultyexportUnpaid">Faculty</label>
                            <select class="form-control faculty" lang="exportUnpaid" id="facultyexportUnpaid" name="faculty">
                                <option value="">All faculties</option>
                                @foreach (($faculty ?? collect()) as $facultyOption)
                                    <option value="{{ $facultyOption->code }}">{{ $facultyOption->title }} ({{ $facultyOption->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="departmentexportUnpaid">Department</label>
                            <select class="form-control department" lang="exportUnpaid" id="departmentexportUnpaid" name="department">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="programexportUnpaid">Programme</label>
                            <select class="form-control" id="programexportUnpaid" name="program">
                                <option value="">All programmes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="levelExportUnpaid">Level</label>
                            <select class="form-control" id="levelExportUnpaid" name="level">
                                <option value="">All levels</option>
                                @foreach (($exportLevels ?? collect()) as $exportLevel)
                                    <option value="{{ $exportLevel }}">{{ $exportLevel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3"><i class="fas fa-file-excel me-1"></i>The exported workbook contains the filtered rows, totals, filters used, and outstanding amount summary.</small>
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
<div class="modal fade" id="exportPaidStudentsModal" tabindex="-1" role="dialog" aria-labelledby="exportPaidStudentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <div>
                    <h5 class="modal-title mb-1" id="exportPaidStudentsLabel">Export students who paid</h5>
                    <small>Filter the paid programme-fee records before downloading the workbook.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportPaidStudentsForm" action="{{ route('admin.receipts.export_paid_students') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="exportPaidSession">Session <span class="text-danger">*</span></label>
                            <select class="form-control" id="exportPaidSession" name="session" required>
                                <option value="">Select session</option>
                                @foreach ($session as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="exportPaidSponsor">Sponsor</label>
                            <select class="form-control" id="exportPaidSponsor" name="fees_type">
                                <option value="">All sponsors</option>
                                <option value="nelfund">NELFUND</option>
                                <option value="others">Others (Self-sponsored)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="facultyexportPaid">Faculty</label>
                            <select class="form-control faculty" lang="exportPaid" id="facultyexportPaid" name="faculty">
                                <option value="">All faculties</option>
                                @foreach (($faculty ?? collect()) as $facultyOption)
                                    <option value="{{ $facultyOption->code }}">{{ $facultyOption->title }} ({{ $facultyOption->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="departmentexportPaid">Department</label>
                            <select class="form-control department" lang="exportPaid" id="departmentexportPaid" name="department">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="programexportPaid">Programme</label>
                            <select class="form-control" id="programexportPaid" name="program">
                                <option value="">All programmes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="levelExportPaid">Level</label>
                            <select class="form-control" id="levelExportPaid" name="level">
                                <option value="">All levels</option>
                                @foreach (($exportLevels ?? collect()) as $exportLevel)
                                    <option value="{{ $exportLevel }}">{{ $exportLevel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3"><i class="fas fa-file-excel me-1"></i>With a faculty, department, programme or level selected, the workbook includes matching students even when they have no payment. IDs follow NN/NN/NN/NNNN, with an optional final letter; records without a valid matric number are omitted. Each row is marked No payment, Partially paid, Fully paid or Overpaid, with a complete summary.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="exportPaidStudentsForm" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
