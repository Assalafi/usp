@php
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;

    $student = DB::table('students')
        ->where('user_id', session('id'))
        ->select('fullname', 'username', 'faculty', 'department', 'program', 'level', 'level_of_entry', 'session_of_entry')
        ->first();

    $invoices = collect($data ?? [])->sortByDesc(function ($invoice) {
        return $invoice->updated_at ?? $invoice->created_at ?? '';
    });
    $pendingProgramme = $invoices->filter(fn ($invoice) =>
        ($invoice->description ?? '') === 'UNIVERSITY OF MAIDUGURI-1000127 FEES' &&
        ($invoice->status ?? '') === 'Pending'
    );
    $pendingIdCards = $invoices->filter(fn ($invoice) =>
        ($invoice->description ?? '') === 'ID CARDS' &&
        ($invoice->status ?? '') === 'Pending'
    );
    $paidInvoices = $invoices->filter(fn ($invoice) => ($invoice->status ?? '') === 'Paid');

    $paymentData = [];
    $totalDue = 0;
    $totalPaid = 0;
    $schoolStatus = true;
    $currentSession = session('system_session');
    $entrySession = $student?->session_of_entry;
    $programCode = $student?->program;
    $entryLevel = $student?->level_of_entry;

    if ($student) {
        if ($currentSession === session('student_session')) {
            $due = (float) (DB::table('school_fees')
                ->where(['program' => $programCode, 'level' => $entryLevel, 'type' => 'NEW'])
                ->value('amount') ?? 0);
            $paid = (float) $paidInvoices
                ->filter(fn ($invoice) => ($invoice->description ?? '') === 'UNIVERSITY OF MAIDUGURI-1000127 FEES')
                ->sum('amount');
            $totalDue = $due;
            $totalPaid = $paid;
            $paymentData[] = ['session' => $currentSession, 'amount' => $due, 'paid' => $paid];
        } else {
            $history = DB::table('session_history')->where('username', session('id_number'))->orderBy('session')->get();
            $hasEligibleSession = false;

            foreach ($history as $record) {
                $status = strtoupper((string) ($record->status ?? ''));
                if (in_array($status, ['PROCEED', 'REPEAT', 'PENDING'], true)) {
                    $hasEligibleSession = true;
                }

                $feeType = $record->session === $entrySession ? 'NEW' : 'RETURNING';
                $due = (float) (DB::table('school_fees')
                    ->where(['program' => $programCode, 'level' => $record->level, 'type' => $feeType])
                    ->value('amount') ?? 0);
                $paid = (float) $paidInvoices
                    ->filter(fn ($invoice) =>
                        ($invoice->description ?? '') === 'UNIVERSITY OF MAIDUGURI-1000127 FEES' &&
                        ($invoice->session ?? '') === $record->session
                    )
                    ->sum('amount');

                $totalDue += $due;
                $totalPaid += $paid;
                $paymentData[] = ['session' => $record->session, 'amount' => $due, 'paid' => $paid];
            }

            $schoolStatus = $hasEligibleSession;
        }
    }

    $remaining = max(0, $totalDue - $totalPaid);
    $programmePaid = $totalPaid > 0;
    $programmeCovered = $totalDue > 0 && $remaining <= 0;
    $programmeConfigured = $totalDue > 0;
    $idCardFee = (float) \App\Http\Controllers\SystemSettingsController::get('id_card_fee', 2000);
    $programTitle = $programCode ? DB::table('program')->where('code', $programCode)->value('title') : null;
@endphp

<div class="main-body ug-payment-page">
    <div class="page-wrapper">
        @if (!$student)
            <div class="alert alert-danger shadow-sm">Your student record could not be found. Please contact Student Affairs.</div>
        @else
            <div class="payment-summary-grid mb-4">
                <div class="payment-summary-card summary-primary">
                    <span class="summary-icon"><i class="fas fa-graduation-cap"></i></span>
                    <div><small>Programme</small><strong>{{ $programTitle ?: $programCode ?: 'Not set' }}</strong><span>Current level: {{ $student->level ?: 'Not set' }}</span></div>
                </div>
                <div class="payment-summary-card summary-info">
                    <span class="summary-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div><small>Total programme fee</small><strong>₦{{ number_format($totalDue, 2) }}</strong><span>{{ count($paymentData) }} session{{ count($paymentData) === 1 ? '' : 's' }} recorded</span></div>
                </div>
                <div class="payment-summary-card summary-success">
                    <span class="summary-icon"><i class="fas fa-check-circle"></i></span>
                    <div><small>Paid so far</small><strong>₦{{ number_format($totalPaid, 2) }}</strong><span>{{ $programmeCovered ? 'Programme fee covered' : ($programmePaid ? 'Part payment recorded' : 'No payment recorded') }}</span></div>
                </div>
                <div class="payment-summary-card {{ $remaining > 0 ? 'summary-warning' : 'summary-success' }}">
                    <span class="summary-icon"><i class="fas {{ $remaining > 0 ? 'fa-wallet' : 'fa-check' }}"></i></span>
                    <div><small>Outstanding balance</small><strong>₦{{ number_format($remaining, 2) }}</strong><span>Registration is not blocked</span></div>
                </div>
            </div>

            <div class="payment-action-grid mb-4">
                <button type="button" class="payment-action-card" data-bs-toggle="modal" data-bs-target="#schoolFeesModal">
                    <span class="action-icon action-blue"><i class="fas fa-school"></i></span>
                    <span><strong>Programme fees</strong><small>View balance and generate an invoice</small></span>
                    <i class="fas fa-chevron-right action-chevron"></i>
                </button>
                <button type="button" class="payment-action-card" data-bs-toggle="modal" data-bs-target="#idCardModal">
                    <span class="action-icon action-purple"><i class="fas fa-id-card"></i></span>
                    <span><strong>ID card payment</strong><small>Generate or complete your ID card fee</small></span>
                    <i class="fas fa-chevron-right action-chevron"></i>
                </button>
                <button type="button" class="payment-action-card" data-bs-toggle="modal" data-bs-target="#verifyInvoiceModal">
                    <span class="action-icon action-green"><i class="fas fa-search-dollar"></i></span>
                    <span><strong>Verify a payment</strong><small>Check any Remita RRR status</small></span>
                    <i class="fas fa-chevron-right action-chevron"></i>
                </button>
            </div>

            <section class="card border-0 shadow-sm payment-history-card">
                <div class="card-header payment-section-header">
                    <div><h5><i class="fas fa-history"></i> Payment history</h5><p>Pending payments can be completed with the secure Remita PayNow widget.</p></div>
                    <span class="history-count">{{ $invoices->count() }} record{{ $invoices->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($invoices->isEmpty())
                        <div class="empty-payment-state"><i class="fas fa-receipt"></i><h6>No payment records yet</h6><p>Generate a programme-fee or ID-card invoice to get started.</p></div>
                    @else
                        <div class="table-responsive">
                            <table class="table payment-history-table mb-0">
                                <thead><tr><th>Payment</th><th>Session</th><th>Amount</th><th>Reference</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        @php
                                            $status = strtolower((string) ($invoice->status ?? 'unknown'));
                                            $isPending = $status === 'pending' && filled($invoice->rrr);
                                        @endphp
                                        <tr>
                                            <td data-label="Payment"><strong>{{ $invoice->description ?: 'Payment' }}</strong><small>{{ $invoice->payment_method ?? 'Remita' }}</small></td>
                                            <td data-label="Session">{{ $invoice->session ?: '—' }}</td>
                                            <td data-label="Amount"><strong>₦{{ number_format((float) $invoice->amount, 2) }}</strong><small>{{ ($invoice->fees_type ?? '') === 'nelfund' ? 'NELFUND' : 'Self sponsor' }}</small></td>
                                            <td data-label="Reference"><span class="reference-value">{{ $invoice->rrr ?: ($invoice->orderId ?: 'N/A') }}</span></td>
                                            <td data-label="Status"><span class="payment-status status-{{ $status }}"><i class="fas {{ $status === 'paid' ? 'fa-check-circle' : ($status === 'pending' ? 'fa-clock' : 'fa-info-circle') }}"></i>{{ ucfirst($status) }}</span></td>
                                            <td data-label="Action" class="text-end">
                                                @if ($isPending)
                                                    <div class="payment-row-actions">
                                                        <button type="button" class="btn btn-primary btn-sm js-paynow" data-rrr="{{ $invoice->rrr }}" data-amount="{{ (float) $invoice->amount }}" aria-label="Pay this pending invoice" title="Pay this pending invoice"><i class="fas fa-credit-card"></i> Pay now</button>
                                                        <a class="btn btn-light btn-sm" href="{{ url('/verify/' . urlencode($invoice->rrr)) }}" aria-label="Verify this payment" title="Verify this payment"><i class="fas fa-sync-alt"></i><span class="d-none d-lg-inline"> Verify</span></a>
                                                        <form action="{{ route('student.payment.cancel', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this pending payment? You can generate a new invoice afterwards.');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Cancel this pending payment" title="Cancel this pending payment"><i class="fas fa-times"></i><span class="d-none d-lg-inline"> Cancel</span></button>
                                                        </form>
                                                    </div>
                                                @elseif ($status === 'paid')
                                                    <a class="btn btn-outline-success btn-sm" href="{{ route('print.receipt', $invoice->rrr ?: '0') }}"><i class="fas fa-download"></i> Receipt</a>
                                                @else
                                                    <span class="text-muted small">No action</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>

@if ($student)
    <div class="modal fade" id="schoolFeesModal" tabindex="-1" aria-labelledby="schoolFeesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content payment-modal">
            <div class="modal-header"><div><span class="modal-kicker">PROGRAMME FEES</span><h5 class="modal-title" id="schoolFeesModalLabel">Programme fee payment</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="fee-overview-grid">
                    <div><small>Total assessed</small><strong>₦{{ number_format($totalDue, 2) }}</strong></div><div><small>Paid</small><strong class="text-success">₦{{ number_format($totalPaid, 2) }}</strong></div><div><small>Balance</small><strong class="{{ $remaining > 0 ? 'text-danger' : ($programmeConfigured ? 'text-success' : 'text-warning') }}">₦{{ number_format($remaining, 2) }}</strong></div>
                </div>
                @if ($paymentData)
                    <div class="session-fee-list">@foreach ($paymentData as $fee)<div class="session-fee-row"><span><strong>{{ $fee['session'] }}</strong><small>Amount paid: ₦{{ number_format($fee['paid'], 2) }}</small></span><strong>₦{{ number_format($fee['amount'], 2) }}</strong></div>@endforeach</div>
                @endif
                @if ($pendingProgramme->isNotEmpty())
                    <div class="pending-notice"><i class="fas fa-clock"></i><div><strong>Pending invoice available</strong><span>Complete it with the PayNow button in your payment history.</span></div></div>
                @elseif (!$programmeConfigured)
                    <div class="pending-notice fee-not-ready"><i class="fas fa-info-circle"></i><div><strong>Fee schedule not available</strong><span>Your programme fee has not been configured for this session yet. Please try again later or contact the accounts office.</span></div></div>
                @elseif ($remaining > 0 && $schoolStatus)
                    <form action="{{ url('/invoices/school-fees') }}" method="GET" class="payment-generate-form">
                        <input type="hidden" name="page" value="school-fees"><input type="hidden" name="try" value="second">
                        <label for="programmeAmount">Amount to pay</label>
                        <select id="programmeAmount" name="amount" class="form-control" required>
                            <option value="">Select an amount</option>
                            @if ($programmePaid)<option value="{{ $remaining }}">Full balance — ₦{{ number_format($remaining, 2) }}</option>@else<option value="{{ $remaining }}">Full payment — ₦{{ number_format($remaining, 2) }}</option><option value="{{ $remaining * 0.5 }}">Half payment — ₦{{ number_format($remaining * 0.5, 2) }}</option>@endif
                        </select>
                        <label for="programmeEmail">Active email address</label><input id="programmeEmail" type="email" name="email" class="form-control" placeholder="you@example.com" required>
                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-file-invoice-dollar"></i> Generate Remita invoice</button>
                    </form>
                @elseif (!$schoolStatus)
                    <div class="empty-payment-state compact"><i class="fas fa-hourglass-half"></i><h6>Academic status pending</h6><p>Programme fees will be available when your academic status is updated.</p></div>
                @else
                    <div class="success-notice"><i class="fas fa-check-circle"></i><div><strong>Programme fee covered</strong><span>No programme-fee balance is currently due.</span></div></div>
                @endif
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="idCardModal" tabindex="-1" aria-labelledby="idCardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md"><div class="modal-content payment-modal">
            <div class="modal-header"><div><span class="modal-kicker">STUDENT ID CARD</span><h5 class="modal-title" id="idCardModalLabel">ID card payment</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="id-card-fee-banner"><i class="fas fa-id-card"></i><div><small>Current fee</small><strong>₦{{ number_format($idCardFee, 2) }}</strong></div></div>
                @if ($pendingIdCards->isNotEmpty())
                    <div class="pending-notice"><i class="fas fa-clock"></i><div><strong>Pending ID-card invoice available</strong><span>Use Pay now in your payment history to complete it.</span></div></div>
                @else
                    <form action="{{ url('/invoices/id-card') }}" method="GET" class="payment-generate-form"><input type="hidden" name="page" value="id-card"><input type="hidden" name="try" value="second"><label for="idPhone">Active phone number</label><input id="idPhone" type="tel" name="phone" class="form-control" placeholder="080..." required><label for="idEmail">Active email address</label><input id="idEmail" type="email" name="email" class="form-control" placeholder="you@example.com" required><button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-file-invoice-dollar"></i> Generate Remita invoice</button></form>
                @endif
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="verifyInvoiceModal" tabindex="-1" aria-labelledby="verifyInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content payment-modal"><div class="modal-header"><div><span class="modal-kicker">REMITA</span><h5 class="modal-title" id="verifyInvoiceModalLabel">Verify payment</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form action="{{ url('/verify') }}" method="GET"><div class="modal-body"><label for="verifyRrr">Remita Retrieval Reference</label><input id="verifyRrr" name="rrr" class="form-control form-control-lg" placeholder="Enter your RRR" required><small class="text-muted d-block mt-2">Use the 12-digit reference on your Remita invoice.</small></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Verify</button></div></form></div></div>
    </div>
@endif

<style>
    .ug-payment-page { background: #f6f8fc; min-height: calc(100vh - 80px); }
    .payment-hero { background: linear-gradient(135deg,#0b4edb,#153b8f); color:#fff; overflow:hidden; }
    .payment-hero-content { display:flex; justify-content:space-between; gap:24px; align-items:center; padding:32px; }
    .payment-eyebrow { display:inline-flex; gap:8px; align-items:center; text-transform:uppercase; letter-spacing:.08em; font-size:.72rem; opacity:.8; }
    .payment-hero h2 { margin:8px 0 6px; font-weight:700; }.payment-hero p { margin:0; opacity:.85; }.payment-context { min-width:220px; text-align:right; display:flex; flex-direction:column; gap:4px; font-size:.85rem; opacity:.9; }.payment-context strong { font-size:1.05rem; }.payment-context i { margin-right:5px; }
    .payment-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }.payment-summary-card { background:#fff; border-radius:14px; padding:18px; display:flex; gap:14px; align-items:flex-start; box-shadow:0 4px 16px rgba(24,39,75,.06); border-left:4px solid transparent; }.summary-primary{border-color:#2563eb}.summary-info{border-color:#0891b2}.summary-success{border-color:#16a34a}.summary-warning{border-color:#f59e0b}.summary-icon { width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#eff6ff;color:#2563eb;flex:none; }.summary-info .summary-icon{background:#ecfeff;color:#0891b2}.summary-success .summary-icon{background:#f0fdf4;color:#16a34a}.summary-warning .summary-icon{background:#fffbeb;color:#d97706}.payment-summary-card small,.payment-summary-card span{display:block;color:#6b7280;font-size:.78rem}.payment-summary-card strong{display:block;color:#172033;font-size:1.12rem;line-height:1.35;margin:3px 0}.payment-summary-card > div{min-width:0}.payment-summary-card strong{overflow-wrap:anywhere}
    .payment-action-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }.payment-action-card { border:1px solid #e5eaf2; background:#fff; border-radius:14px; padding:18px; display:flex; align-items:center; gap:13px; text-align:left; transition:.2s; width:100%; }.payment-action-card:hover{border-color:#2563eb; box-shadow:0 8px 24px rgba(37,99,235,.1); transform:translateY(-1px)}.payment-action-card>span:nth-child(2){display:flex;flex-direction:column;flex:1}.payment-action-card strong{color:#172033}.payment-action-card small{color:#6b7280;margin-top:3px}.action-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;flex:none}.action-blue{background:#eff6ff;color:#2563eb}.action-purple{background:#f5f3ff;color:#7c3aed}.action-green{background:#f0fdf4;color:#16a34a}.action-chevron{color:#9ca3af}
    .payment-history-card{overflow:hidden}.payment-section-header{display:flex;justify-content:space-between;align-items:center;gap:16px;background:#fff;padding:20px 24px;border-bottom:1px solid #edf0f5}.payment-section-header h5{margin:0;font-weight:700;color:#172033}.payment-section-header h5 i{color:#2563eb;margin-right:8px}.payment-section-header p{margin:5px 0 0;color:#6b7280;font-size:.84rem}.history-count{background:#eff6ff;color:#2563eb;padding:7px 11px;border-radius:99px;font-size:.78rem;white-space:nowrap}.payment-history-table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;background:#f8fafc;border:0;padding:13px 18px}.payment-history-table tbody td{padding:16px 18px;vertical-align:middle;border-color:#edf0f5;color:#273247}.payment-history-table td strong{display:block}.payment-history-table td small{display:block;color:#7b8494;font-size:.78rem;margin-top:3px}.reference-value{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.82rem;color:#334155}.payment-status{display:inline-flex;align-items:center;gap:6px;border-radius:99px;padding:6px 10px;font-size:.75rem;font-weight:600}.status-paid{background:#ecfdf3;color:#15803d}.status-pending{background:#fff7ed;color:#c2410c}.status-unknown{background:#f1f5f9;color:#475569}.payment-row-actions{display:flex;justify-content:flex-end;gap:6px}.payment-row-actions .btn{white-space:nowrap}.empty-payment-state{text-align:center;padding:48px 24px;color:#6b7280}.empty-payment-state i{font-size:2rem;color:#a8b3c6;margin-bottom:10px}.empty-payment-state h6{color:#273247;margin:0 0 5px}.empty-payment-state p{margin:0}.empty-payment-state.compact{padding:26px 12px}
    .payment-modal{border:0;border-radius:16px;overflow:hidden}.payment-modal .modal-header{padding:20px 24px;border-bottom:1px solid #edf0f5}.modal-kicker{display:block;color:#2563eb;font-size:.68rem;font-weight:700;letter-spacing:.08em;margin-bottom:4px}.payment-modal .modal-title{font-weight:700;color:#172033}.payment-modal .modal-body{padding:24px}.payment-modal label{font-size:.82rem;font-weight:600;color:#374151;margin:14px 0 6px;display:block}.payment-modal label:first-child{margin-top:0}.payment-generate-form .form-control{min-height:46px;border-color:#dbe2ec}.payment-generate-form button{margin-top:20px}.fee-overview-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}.fee-overview-grid>div{background:#f8fafc;border-radius:10px;padding:14px}.fee-overview-grid small{display:block;color:#6b7280;font-size:.74rem}.fee-overview-grid strong{display:block;font-size:1.05rem;margin-top:3px}.session-fee-list{border:1px solid #edf0f5;border-radius:10px;margin-bottom:18px}.session-fee-row{padding:11px 14px;display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #edf0f5}.session-fee-row:last-child{border-bottom:0}.session-fee-row span{display:flex;flex-direction:column}.session-fee-row small{color:#6b7280;font-size:.76rem;margin-top:2px}.pending-notice,.success-notice{display:flex;gap:12px;align-items:flex-start;border-radius:11px;padding:14px;background:#fff7ed;color:#9a3412}.pending-notice>i{margin-top:2px}.pending-notice strong,.success-notice strong{display:block}.pending-notice span,.success-notice span{display:block;font-size:.8rem;margin-top:3px;color:#6b7280}.success-notice{background:#f0fdf4;color:#15803d}.id-card-fee-banner{display:flex;gap:14px;align-items:center;background:#f5f3ff;border-radius:12px;padding:18px;color:#6d28d9;margin-bottom:18px}.id-card-fee-banner i{font-size:1.7rem}.id-card-fee-banner small,.id-card-fee-banner strong{display:block}.id-card-fee-banner small{font-size:.78rem;color:#6b7280}.id-card-fee-banner strong{font-size:1.3rem}
    @media (max-width: 992px){.payment-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.payment-action-grid{grid-template-columns:1fr}.payment-context{text-align:left}}
    @media (max-width: 576px){
        .ug-payment-page{margin:0 -5px;min-height:calc(100vh - 62px)}
        .ug-payment-page .page-wrapper{padding:12px 10px 28px}
        .payment-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-bottom:14px!important}
        .payment-summary-card{min-width:0;min-height:96px;padding:13px 10px;border-left-width:3px;border-radius:11px;gap:8px;box-shadow:0 2px 10px rgba(24,39,75,.06);align-items:flex-start}
        .summary-icon{width:33px;height:33px;border-radius:9px;font-size:.85rem}
        .payment-summary-card small,.payment-summary-card span{font-size:.67rem;line-height:1.25}
        .payment-summary-card strong{font-size:.82rem;line-height:1.25;margin:2px 0;word-break:break-word}
        .payment-action-grid{gap:9px;margin-bottom:14px!important}
        .payment-action-card{min-height:68px;padding:12px 11px;border-radius:11px;gap:10px}
        .action-icon{width:34px;height:34px;border-radius:9px;font-size:.86rem}
        .payment-action-card strong{font-size:.87rem}.payment-action-card small{font-size:.7rem;line-height:1.25}.action-chevron{font-size:.75rem}
        .payment-history-card{border-radius:11px}.payment-section-header{padding:14px 13px;gap:8px;align-items:flex-start}.payment-section-header h5{font-size:.95rem}.payment-section-header h5 i{margin-right:5px}.payment-section-header p{font-size:.7rem;line-height:1.35;margin-top:4px}.history-count{font-size:.65rem;padding:5px 7px}
        .payment-history-table thead{display:none}.payment-history-table,.payment-history-table tbody,.payment-history-table tr,.payment-history-table td{display:block;width:100%}.payment-history-table tr{padding:13px 13px 11px;border-bottom:1px solid #edf0f5}.payment-history-table tr:last-child{border-bottom:0}.payment-history-table td{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;padding:6px 0;border:0;text-align:right;font-size:.79rem;line-height:1.25}.payment-history-table td:before{content:attr(data-label);flex:0 0 auto;font-size:.64rem;text-transform:uppercase;letter-spacing:.04em;color:#7b8494;text-align:left;padding-top:1px}.payment-history-table td:first-child{display:block;text-align:left;padding-top:0;padding-bottom:9px}.payment-history-table td:first-child:before{display:none}.payment-history-table td:first-child strong{font-size:.86rem;line-height:1.3;overflow-wrap:anywhere}.payment-history-table td:first-child small{font-size:.7rem}.payment-history-table td:nth-child(4) .reference-value{max-width:62%;overflow-wrap:anywhere}.payment-row-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));width:100%;gap:6px}.payment-row-actions .btn{min-height:40px;padding:8px 5px;font-size:.72rem}.payment-history-table td:last-child{display:block;text-align:left;padding-top:9px}.payment-history-table td:last-child:before{display:none}
        .empty-payment-state{padding:34px 16px}.empty-payment-state i{font-size:1.6rem}.empty-payment-state h6{font-size:.9rem}.empty-payment-state p{font-size:.75rem}
        .modal-dialog{margin:.5rem}.payment-modal{border-radius:13px}.payment-modal .modal-header{padding:15px 16px}.payment-modal .modal-body{padding:16px}.payment-modal .modal-footer{padding:12px 16px;gap:7px}.payment-modal .modal-footer .btn{flex:1;min-height:44px}.payment-modal .modal-title{font-size:1rem}.payment-modal label{font-size:.76rem}.payment-generate-form .form-control,.payment-modal .form-control{min-height:48px;font-size:16px}.payment-generate-form button{min-height:49px;margin-top:17px;font-size:.88rem}.fee-overview-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}.fee-overview-grid>div{padding:10px 8px}.fee-overview-grid small{font-size:.63rem}.fee-overview-grid strong{font-size:.84rem;overflow-wrap:anywhere}.session-fee-row{padding:10px 11px;font-size:.8rem}.session-fee-row small{font-size:.68rem}.pending-notice,.success-notice{padding:12px;font-size:.8rem}.pending-notice span,.success-notice span{font-size:.72rem}.id-card-fee-banner{padding:14px;margin-bottom:14px}.id-card-fee-banner i{font-size:1.4rem}.id-card-fee-banner strong{font-size:1.15rem}
    }
</style>

<script src="{{ \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl() }}/payment/v1/remita-pay-inline.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        const verifyUrl = @json(url('/verify'));
        const publicKey = @json(env('REMITA_PUBLIC_KEY'));

        function showPaymentError(message) {
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Payment error', text: message });
            } else {
                window.alert(message);
            }
        }

        function launchPayNow(button) {
            const rrr = button.dataset.rrr;
            if (!rrr) {
                showPaymentError('This invoice does not have a valid Remita reference.');
                return;
            }
            if (typeof window.RmPaymentEngine === 'undefined') {
                showPaymentError('The Remita payment widget is still loading. Please try again.');
                return;
            }

            const original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening...';
            let completed = false;

            try {
                const engine = window.RmPaymentEngine.init({
                    key: publicKey,
                    processRrr: true,
                    channels: 'card,bank,branch,ussd,qr,ibank,paywithremita,buyoncredit,wallet,phonenumber,transfer,enaira',
                    transactionId: 'UG-' + Date.now() + '-' + Math.floor(Math.random() * 100000),
                    extendedData: { customFields: [{ name: 'rrr', value: rrr }] },
                    onSuccess: function () {
                        completed = true;
                        window.location.href = verifyUrl + '?rrr=' + encodeURIComponent(rrr);
                    },
                    onError: function (response) {
                        button.disabled = false;
                        button.innerHTML = original;
                        showPaymentError((response && (response.message || response.error)) || 'Payment was not completed.');
                    },
                    onClose: function () {
                        if (!completed) {
                            button.disabled = false;
                            button.innerHTML = original;
                        }
                    }
                });
                engine.showPaymentWidget();
            } catch (error) {
                button.disabled = false;
                button.innerHTML = original;
                showPaymentError('Could not initialize the Remita payment widget. Please try again.');
                console.error(error);
            }
        }

        document.addEventListener('click', function (event) {
            const button = event.target.closest('.js-paynow');
            if (button) {
                event.preventDefault();
                launchPayNow(button);
            }
        });
    })();
</script>
