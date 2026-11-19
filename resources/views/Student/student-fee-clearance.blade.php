<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clear outstanding fees | UNIMAID Portal</title>
    <style>
        :root{--brand:#257fba;--brand2:#48a9e7;--ink:#17324d;--muted:#607589;--line:#dce8f0;--danger:#9c2f37;--danger-bg:#fff3f3;--gold:#a66a00;--gold-bg:#fff8e8;--green:#167447;--green-bg:#effbf4}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:16px;background:linear-gradient(145deg,#eef8fe,#f9fbfd 58%,#eaf1f6);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
        .card{width:min(100%,700px);overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 24px 70px rgba(24,72,105,.16)}
        .verify-head{display:flex;align-items:flex-start;gap:10px;margin-bottom:11px}.verify-badge{display:grid;place-items:center;flex:0 0 auto;width:36px;height:36px;border-radius:11px;background:#e7f3fb;color:#1769aa;font-size:1rem}.verify-head strong{display:block;color:var(--ink);font-size:.92rem}.verify-head span{display:block;margin-top:3px;color:var(--muted);font-size:.78rem;line-height:1.45}.rrr-control{position:relative;display:flex;align-items:center;min-height:52px;border:1px solid #cbdde8;border-radius:12px;background:#fff;transition:border-color .2s,box-shadow .2s}.rrr-control:focus-within{border-color:var(--brand);box-shadow:0 0 0 3px rgba(62,161,228,.14)}.rrr-control .prefix{padding-left:13px;color:#7d94a5;font-size:.78rem;font-weight:800;letter-spacing:.04em}.rrr-control input{min-width:0;flex:1;height:50px;padding:0 9px;border:0;outline:0;background:transparent;color:var(--ink);font-size:1rem;font-weight:700;letter-spacing:.08em}.rrr-control input::placeholder{color:#a4b5c1;font-weight:500;letter-spacing:.01em}.rrr-count{padding-right:13px;color:#8aa0ae;font-size:.72rem;white-space:nowrap}.verify-hint{margin:7px 0 12px;color:var(--muted);font-size:.76rem;line-height:1.45}.verify form{display:block}.verify .btn{width:100%;min-height:46px;white-space:nowrap}
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
            <div class="summary">
                <div><small>Recovery session</small><strong>{{ $balance['session'] }}</strong></div>
                <div class="balance"><small>Amount to clear</small><strong>&#8358;{{ number_format((float) $balance['balance'], 2) }}</strong></div>
            </div>
            <p class="copy">This is the complete programme-fee balance recorded from your earlier academic sessions. Part-payment is not available for this clearance.</p>
            <section class="payment-box" aria-labelledby="payment-heading">
                <h2 id="payment-heading">Complete payment on this page</h2>
                <p class="sub">Generate your Remita reference, open the secure PayNow widget, then verify the payment here.</p>
                @if($pendingInvoice)
                    <div class="pending"><span class="icon">&#8987;</span><div><strong>Payment reference ready</strong><span>RRR: <b>{{ $pendingInvoice->rrr ?: 'Pending reference' }}</b> · Amount: <b>&#8358;{{ number_format((float) ($pendingInvoice->base_amount ?: $pendingInvoice->amount), 2) }}</b></span><div class="pending-actions">
                        @if($pendingInvoice->rrr)<button type="button" class="btn primary js-paynow" data-rrr="{{ $pendingInvoice->rrr }}" data-amount="{{ (float) $pendingInvoice->amount }}"><i class="fas fa-credit-card"></i>&nbsp; Pay now</button><a class="btn secondary js-verify" href="{{ url('/verify/' . urlencode($pendingInvoice->rrr) . '?clearance=1') }}"><i class="fas fa-sync-alt"></i>&nbsp; Verify</a>@endif
                        <form action="{{ route('student.payment.cancel', $pendingInvoice->id) }}?clearance=1" method="POST" class="js-cancel-form">@csrf<button type="submit" class="btn logout"><i class="fas fa-times"></i>&nbsp; Cancel</button></form>
                    </div></div></div>
                @else
                    <form action="{{ url('/invoices/school-fees') }}" method="GET" id="clearanceGenerateForm">
                        <input type="hidden" name="page" value="school-fees"><input type="hidden" name="try" value="second"><input type="hidden" name="amount" value="{{ (float) $balance['balance'] }}"><input type="hidden" name="clearance" value="1">
                        <div class="amount"><span>Full amount required</span><strong>&#8358;{{ number_format((float) $balance['balance'], 2) }}</strong></div>
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
