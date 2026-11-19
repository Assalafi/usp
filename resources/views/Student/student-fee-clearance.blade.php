<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clear outstanding fees | UNIMAID Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root{--brand:#257fba;--brand2:#48a9e7;--ink:#17324d;--muted:#607589;--line:#dce8f0;--danger:#9c2f37;--danger-bg:#fff3f3;--gold:#a66a00;--gold-bg:#fff8e8;--green:#167447;--green-bg:#effbf4}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:16px;background:linear-gradient(145deg,#eef8fe,#f9fbfd 58%,#eaf1f6);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
        .card{width:min(100%,700px);overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 24px 70px rgba(24,72,105,.16)}
        .head{padding:27px 29px 26px;color:#fff;background:linear-gradient(135deg,var(--brand),var(--brand2))}.brand{display:flex;align-items:center;gap:11px;margin-bottom:24px;font-size:.78rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.brand img{width:43px;height:43px;padding:5px;object-fit:contain;background:#fff;border-radius:12px}.head h1{margin:0;font-size:clamp(1.35rem,4vw,1.9rem);line-height:1.2}.head p{margin:9px 0 0;color:rgba(255,255,255,.9);font-size:.93rem;line-height:1.55}.body{padding:28px 29px 30px}.notice{display:flex;gap:12px;align-items:flex-start;margin-bottom:18px;padding:15px 16px;border:1px solid #f0c8c9;border-radius:14px;background:var(--danger-bg);color:#7d2730;font-size:.91rem;line-height:1.55}.notice strong{display:block;margin-bottom:3px;color:var(--danger)}.icon{display:grid;place-items:center;flex:0 0 auto;width:34px;height:34px;border-radius:10px;background:#f8dfe0;font-weight:900}.person{margin-bottom:18px;color:var(--muted);font-size:.89rem}.person strong{display:block;color:var(--ink);font-size:1rem}.summary{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0}.summary div{padding:15px;border:1px solid var(--line);border-radius:14px;background:#f8fbfd}.summary small{display:block;margin-bottom:6px;color:var(--muted);font-size:.76rem}.summary strong{display:block;color:var(--ink);font-size:1.12rem}.summary .balance{border-color:#f0d9a5;background:var(--gold-bg)}.summary .balance strong{color:var(--gold)}.copy{margin:0 0 18px;color:var(--muted);font-size:.91rem;line-height:1.7}.payment-box{padding:19px;border:1px solid var(--line);border-radius:16px;background:#fbfdff}.payment-box h2{margin:0;color:var(--ink);font-size:1.04rem}.payment-box .sub{margin:5px 0 16px;color:var(--muted);font-size:.82rem;line-height:1.5}.fields{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field label{display:block;margin-bottom:6px;color:var(--ink);font-size:.78rem;font-weight:800}.field input{width:100%;height:46px;padding:0 12px;border:1px solid #cbdde8;border-radius:11px;color:var(--ink);font-size:.95rem;outline:none}.field input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(62,161,228,.14)}.amount{display:flex;justify-content:space-between;gap:10px;align-items:center;margin:14px 0;padding:12px 13px;border-radius:11px;background:var(--gold-bg);color:var(--gold);font-size:.83rem}.amount strong{font-size:1.1rem}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 17px;border:0;border-radius:11px;font-size:.84rem;font-weight:800;text-decoration:none;cursor:pointer}.primary{width:100%;color:#fff;background:#1769aa}.primary:hover{color:#fff;background:#115889}.secondary{color:#1769aa;border:1px solid #b8d9ec;background:#f1f9fe}.logout{color:var(--danger);border:1px solid #e4c6c8;background:#fff}.pending{display:flex;gap:12px;align-items:flex-start;margin-bottom:16px;padding:14px;border:1px solid #f0d9a5;border-radius:12px;background:var(--gold-bg);color:#805800}.pending strong{display:block;color:#855d00}.pending span{display:block;margin-top:3px;color:#765f32;font-size:.78rem;line-height:1.45}.pending-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.pending-actions form{margin:0}.pending-actions .btn{min-height:39px;padding:0 12px;font-size:.76rem}.success{display:flex;gap:10px;align-items:flex-start;margin:16px 0;padding:13px 14px;border:1px solid #b9e3c9;border-radius:12px;background:var(--green-bg);color:var(--green);font-size:.83rem;line-height:1.5}.verify{margin-top:18px;padding-top:17px;border-top:1px solid #edf2f5}.verify form{display:flex;gap:8px}.verify input{flex:1;height:44px;padding:0 12px;border:1px solid #cbdde8;border-radius:10px;font-size:.88rem}.verify .btn{min-height:44px;white-space:nowrap}.foot{margin-top:20px;padding-top:17px;border-top:1px solid #edf2f5;color:#7b8d9b;font-size:.76rem;line-height:1.5;text-align:center}.spinner{display:inline-block;width:15px;height:15px;margin-right:7px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;vertical-align:-2px;animation:spin .7s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}@media(max-width:560px){body{padding:10px}.card{border-radius:18px}.head,.body{padding:22px 19px}.summary,.fields{grid-template-columns:1fr}.payment-box{padding:15px}.verify form{flex-direction:column}.verify .btn{width:100%}.actions{flex-direction:column}.actions .logout{order:2}.actions .btn{width:100%}}
        .verify-head{display:flex;align-items:flex-start;gap:10px;margin-bottom:11px}.verify-badge{display:grid;place-items:center;flex:0 0 auto;width:36px;height:36px;border-radius:11px;background:#e7f3fb;color:#1769aa;font-size:1rem}.verify-head strong{display:block;color:var(--ink);font-size:.92rem}.verify-head span{display:block;margin-top:3px;color:var(--muted);font-size:.78rem;line-height:1.45}.rrr-control{position:relative;display:flex;align-items:center;min-height:52px;border:1px solid #cbdde8;border-radius:12px;background:#fff;transition:border-color .2s,box-shadow .2s}.rrr-control:focus-within{border-color:var(--brand);box-shadow:0 0 0 3px rgba(62,161,228,.14)}.rrr-control .prefix{padding-left:13px;color:#7d94a5;font-size:.78rem;font-weight:800;letter-spacing:.04em}.rrr-control input{min-width:0;flex:1;height:50px;padding:0 9px;border:0;outline:0;background:transparent;color:var(--ink);font-size:1rem;font-weight:700;letter-spacing:.08em}.rrr-control input::placeholder{color:#a4b5c1;font-weight:500;letter-spacing:.01em}.rrr-count{padding-right:13px;color:#8aa0ae;font-size:.72rem;white-space:nowrap}.verify-hint{margin:7px 0 12px;color:var(--muted);font-size:.76rem;line-height:1.45}.verify form{display:block}.verify .btn{width:100%;min-height:46px;white-space:nowrap}.amount small{display:block;margin-top:3px;color:#8b6a2d;font-size:.7rem;line-height:1.3}.pending-charge{display:block;margin-top:5px;color:#765f32!important;font-size:.74rem!important;line-height:1.35!important}
        .history{margin-top:20px;border:1px solid var(--line);border-radius:16px;background:#fff;overflow:hidden}.history summary{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 17px;color:var(--ink);font-size:.92rem;font-weight:800;cursor:pointer;list-style:none}.history summary::-webkit-details-marker{display:none}.history summary:after{content:'+';display:grid;place-items:center;width:25px;height:25px;border-radius:8px;background:#edf6fc;color:#1769aa;font-size:1.1rem}.history[open] summary:after{content:'−'}.history-caption{padding:0 17px 13px;color:var(--muted);font-size:.77rem;line-height:1.45}.session-list{display:grid;gap:9px;padding:0 13px 15px}.session-row{display:grid;grid-template-columns:1.1fr .9fr .9fr .9fr;gap:8px;padding:12px;border:1px solid #e6eef3;border-radius:11px;background:#f9fcfe}.session-row small{display:block;margin-bottom:4px;color:var(--muted);font-size:.69rem}.session-row strong{display:block;color:var(--ink);font-size:.83rem}.session-row .remaining strong{color:var(--gold)}.history-table-wrap{padding:0 13px 15px;overflow-x:auto}.history-table{width:100%;min-width:560px;border-collapse:collapse;font-size:.76rem}.history-table th{padding:9px 8px;border-bottom:1px solid #dce8f0;color:#607589;text-align:left;font-size:.68rem;letter-spacing:.04em;text-transform:uppercase}.history-table td{padding:10px 8px;border-bottom:1px solid #edf2f5;color:var(--ink);vertical-align:top}.history-table tr:last-child td{border-bottom:0}.history-empty{padding:5px 13px 16px;color:var(--muted);font-size:.78rem}.history-status{display:inline-block;padding:3px 7px;border-radius:999px;background:#effbf4;color:#167447;font-size:.68rem;font-weight:800}.history-status.clearance{background:#edf6fc;color:#1769aa}
    </style>
</head>
<body>
    <main class="card" aria-labelledby="fee-title">
        <section class="head">
            <div class="brand"><img src="{{ asset('uploads/logo-transparent.png') }}" alt="University of Maiduguri crest"><span>University of Maiduguri Portal</span></div>
            <h1 id="fee-title">Clear your outstanding programme fees</h1>
            <p>Payment is required before you can continue into the 2026/2027 academic session.</p>
        </section>
        <section class="body">
            @if(session('error'))<div class="notice"><span class="icon">!</span><div>{{ session('error') }}</div></div>@endif
            @if(session('info'))<div class="notice"><span class="icon">i</span><div>{{ session('info') }}</div></div>@endif
            @if(session('success'))<div class="success"><strong>Payment update:</strong><span>{{ session('success') }}</span></div>@endif
            <div class="notice" role="alert"><span class="icon">!</span><div><strong>Portal access is temporarily restricted</strong>Please settle the complete outstanding balance below. Until Remita confirms the payment, only this page and logout are available.</div></div>
            <div class="person"><strong>{{ $student->fullname }}</strong>{{ $student->username }}</div>
            <p class="copy">Please complete the full programme-fee balance below. The amount shown for payment includes the configured service charge. A detailed session-by-session breakdown and payment history is available after the payment section.</p>
            <section class="payment-box" aria-labelledby="payment-heading">
                <h2 id="payment-heading">Complete payment on this page</h2>
                <p class="sub">Generate your Remita reference, open the secure PayNow widget, then verify the payment here.</p>
                @if($pendingInvoice)
                    <div class="pending"><span class="icon">&#8987;</span><div><strong>Payment reference ready</strong><span>RRR: <b>{{ $pendingInvoice->rrr ?: 'Pending reference' }}</b> · Amount: <b>&#8358;{{ number_format((float) ($pendingInvoice->base_amount ?: $pendingInvoice->amount), 2) }}</b></span><div class="pending-actions">
                        @if(($paymentSurcharge ?? 0) > 0)<small class="pending-charge">This total includes the service charge of &#8358;{{ number_format((float) $paymentSurcharge, 2) }}.</small>@endif
                        @if($pendingInvoice->rrr)<button type="button" class="btn primary js-paynow" data-rrr="{{ $pendingInvoice->rrr }}" data-amount="{{ (float) $pendingInvoice->amount }}"><i class="fas fa-credit-card"></i>&nbsp; Pay now</button><a class="btn secondary js-verify" href="{{ url('/verify/' . urlencode($pendingInvoice->rrr) . '?clearance=1') }}"><i class="fas fa-sync-alt"></i>&nbsp; Verify</a>@endif
                        <form action="{{ route('student.payment.cancel', $pendingInvoice->id) }}?clearance=1" method="POST" class="js-cancel-form">@csrf<button type="submit" class="btn logout"><i class="fas fa-times"></i>&nbsp; Cancel</button></form>
                    </div></div></div>
                @else
                    <form action="{{ url('/invoices/school-fees') }}" method="GET" id="clearanceGenerateForm">
                        <input type="hidden" name="page" value="school-fees"><input type="hidden" name="try" value="second"><input type="hidden" name="amount" value="{{ (float) $balance['balance'] }}"><input type="hidden" name="clearance" value="1">
                        <div class="amount"><div><span>Total payable</span>@if(($paymentSurcharge ?? 0) > 0)<small>Includes service charge of &#8358;{{ number_format((float) $paymentSurcharge, 2) }}</small>@endif</div><strong>&#8358;{{ number_format((float) ($paymentTotal ?? $balance['balance']), 2) }}</strong></div>
                        <div class="fields"><div class="field"><label for="clearanceEmail">Email address</label><input id="clearanceEmail" type="email" name="email" value="{{ old('email', session('email')) }}" placeholder="you@example.com" required></div><div class="field"><label for="clearancePhone">Phone number</label><input id="clearancePhone" type="tel" name="phone" value="{{ old('phone', session('phone')) }}" placeholder="080..." required></div></div>
                        <button type="submit" class="btn primary" style="margin-top:15px"><i class="fas fa-file-invoice-dollar"></i>&nbsp; Generate Remita reference</button>
                    </form>
                @endif
                <div class="verify">
                    <div class="verify-head"><span class="verify-badge">✓</span><div><strong>Already paid or completed the bank step?</strong><span>Confirm your Remita Retrieval Reference (RRR) to update your payment status.</span></div></div>
                    <form action="{{ url('/verify') }}" method="GET" id="clearanceVerifyForm">
                        <input type="hidden" name="clearance" value="1">
                        <div class="rrr-control"><span class="prefix">RRR</span><input id="clearanceRrr" name="rrr" inputmode="numeric" autocomplete="off" pattern="[0-9]{12}" maxlength="12" minlength="12" placeholder="Enter 12 digits" aria-label="12-digit Remita Retrieval Reference" required><span class="rrr-count" id="rrrCount">0/12</span></div>
                        <p class="verify-hint">Your RRR is the 12-digit number on your Remita receipt or payment confirmation. Enter numbers only.</p>
                        <button type="submit" class="btn secondary"><i class="fas fa-check"></i>&nbsp; Verify payment</button>
                    </form>
                </div>
            </section>
            <details class="history">
                <summary><span>Payment history and session balances</span><span class="sr-only">Show details</span></summary>
                <p class="history-caption">This reference shows how the outstanding amount was calculated. The payment above remains the only action required.</p>
                <div class="session-list">
                    @forelse($sessionBreakdown as $row)
                        <div class="session-row">
                            <div><small>Academic session</small><strong>{{ $row->session }}</strong></div>
                            <div><small>Expected</small><strong>&#8358;{{ number_format((float) $row->assessed, 2) }}</strong></div>
                            <div><small>Paid</small><strong>&#8358;{{ number_format((float) $row->paid, 2) }}</strong></div>
                            <div class="remaining"><small>Remaining</small><strong>&#8358;{{ number_format((float) $row->remaining, 2) }}</strong></div>
                        </div>
                    @empty
                        <div class="history-empty">No session breakdown is available yet.</div>
                    @endforelse
                </div>
                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead><tr><th>Date</th><th>Session</th><th>Reference</th><th>Amount paid</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($paymentHistory as $payment)
                                <tr><td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y') : '—' }}</td><td>{{ $payment->session ?: 'Clearance' }}</td><td>{{ $payment->rrr ?: '—' }}</td><td>&#8358;{{ number_format((float) ($payment->base_amount ?: $payment->amount), 2) }}</td><td><span class="history-status {{ (int) ($payment->fee_clearance ?? 0) === 1 ? 'clearance' : '' }}">{{ (int) ($payment->fee_clearance ?? 0) === 1 ? 'Clearance payment' : 'Paid' }}</span></td></tr>
                            @empty
                                <tr><td colspan="5" class="history-empty">No programme-fee payment has been recorded for the affected sessions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </details>
            <div class="actions"><a class="btn logout" href="{{ url('/logout') }}">Log out</a></div>
            <div class="foot">Once Remita confirms the full payment, this restriction is removed automatically. Do not make another payment while a reference is pending.</div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script id="remita-widget-script" src="{{ \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl() }}/payment/v1/remita-pay-inline.bundle.js"></script>
    <script>
        (() => {
            const verifyUrl = @json(url('/verify'));
            const publicKey = @json(config('services.remita.public_key'));
            let scriptError = false;
            document.getElementById('remita-widget-script')?.addEventListener('error', () => { scriptError = true; }, { once:true });
            const feedback = (message) => { if (window.Swal) Swal.fire({icon:'error',title:'Payment error',text:message}); else alert(message); };
            const waitForRemita = () => new Promise((resolve,reject) => { const start=Date.now(); const tick=()=>{ if(window.RmPaymentEngine) return resolve(); if(scriptError || Date.now()-start>15000) return reject(new Error('The Remita payment widget could not be loaded. Please try again.')); setTimeout(tick,100); }; tick(); });
            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.js-paynow'); if (!button) return; event.preventDefault();
                const original = button.innerHTML; button.disabled=true; button.innerHTML='<span class="spinner"></span>Opening payment...'; let complete=false;
                try { await waitForRemita(); const engine=window.RmPaymentEngine.init({key:publicKey,processRrr:true,channels:'card,bank,branch,ussd,qr,ibank,paywithremita,buyoncredit,wallet,phonenumber,transfer,enaira',transactionId:'UG-CLEARANCE-'+Date.now(),extendedData:{customFields:[{name:'rrr',value:button.dataset.rrr}]},onSuccess:()=>{complete=true; window.location.href=verifyUrl+'?rrr='+encodeURIComponent(button.dataset.rrr)+'&clearance=1';},onError:(response)=>{button.disabled=false;button.innerHTML=original;feedback(response?.message||response?.error||'Payment was not completed.');},onClose:()=>{if(!complete){button.disabled=false;button.innerHTML=original;}}}); engine.showPaymentWidget(); } catch(error){button.disabled=false;button.innerHTML=original;feedback(error.message);}
            });
            document.getElementById('clearanceGenerateForm')?.addEventListener('submit', function(){ const b=this.querySelector('button'); b.disabled=true; b.innerHTML='<span class="spinner"></span>Generating reference...'; });
            const rrrInput = document.getElementById('clearanceRrr');
            const rrrCount = document.getElementById('rrrCount');
            const updateRrr = () => {
                if (!rrrInput) return;
                const digits = rrrInput.value.replace(/\D/g, '').slice(0, 12);
                if (rrrInput.value !== digits) rrrInput.value = digits;
                if (rrrCount) rrrCount.textContent = `${digits.length}/12`;
                rrrInput.setCustomValidity(digits.length === 12 ? '' : 'Enter the complete 12-digit RRR.');
            };
            rrrInput?.addEventListener('input', updateRrr);
            updateRrr();
            document.querySelectorAll('.js-cancel-form').forEach((form) => form.addEventListener('submit', (event) => {
                event.preventDefault();
                const confirmAction = window.Swal
                    ? Swal.fire({
                        icon: 'warning',
                        title: 'Cancel this payment reference?',
                        text: 'The pending reference will be cancelled. You can generate a new reference afterwards.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, cancel it',
                        cancelButtonText: 'Keep reference',
                        confirmButtonColor: '#b42318',
                        cancelButtonColor: '#1769aa',
                        reverseButtons: true,
                        focusCancel: true,
                    })
                    : Promise.resolve({ isConfirmed: window.confirm('Cancel this payment reference?') });
                confirmAction.then((result) => {
                    if (!result.isConfirmed) return;
                    const button = form.querySelector('button');
                    if (button) { button.disabled = true; button.innerHTML = '<span class="spinner"></span>Cancelling...'; }
                    HTMLFormElement.prototype.submit.call(form);
                });
            }));
            document.querySelectorAll('.js-verify,#clearanceVerifyForm').forEach((el)=>el.addEventListener('submit',()=>{ const b=el.matches('form')?el.querySelector('button'):el; if(b){b.innerHTML='<span class="spinner"></span>Checking...';b.setAttribute('aria-disabled','true');if(b.tagName==='BUTTON')b.disabled=true;} }));
        })();
    </script>
</body>
</html>
