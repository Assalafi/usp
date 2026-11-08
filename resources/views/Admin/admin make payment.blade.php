@php
    $services = collect($paymentServices ?? []);
    $sessions = collect($paymentSessions ?? []);
    $paymentResult = session('adminPaymentResult');
    $currentPaymentSession = $paymentSession ?? session('system_session');
@endphp

<style>
    .admin-payment-page{--ap-ink:#17324d;--ap-muted:#6d7f91;--ap-line:#e3edf4;--ap-blue:#116eb5;min-height:calc(100vh - 80px);background:#f5f8fc;padding:18px 12px 42px}.admin-payment-wrap{max-width:1160px;margin:0 auto}.ap-hero{border-radius:22px;padding:27px 30px;color:#fff;background:linear-gradient(125deg,#0e477c,#1377bd 60%,#2ca9d2);box-shadow:0 16px 34px rgba(21,76,119,.16);position:relative;overflow:hidden}.ap-hero:after{content:"";position:absolute;width:280px;height:280px;right:-80px;top:-140px;border-radius:50%;background:rgba(255,255,255,.11)}.ap-hero>*{position:relative;z-index:1}.ap-kicker{font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;font-weight:800;opacity:.82;margin:0 0 6px}.ap-hero h1{font-size:clamp(1.55rem,3vw,2.1rem);font-weight:800;margin:0}.ap-hero p{color:rgba(255,255,255,.84);max-width:680px;line-height:1.55;margin:8px 0 0}.ap-hero .btn{border-radius:10px;font-weight:700}.ap-layout{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(280px,.8fr);gap:16px;margin-top:16px}.ap-card{background:#fff;border:1px solid var(--ap-line);border-radius:17px;box-shadow:0 7px 18px rgba(30,67,98,.05);overflow:hidden}.ap-card-head{padding:18px 20px;border-bottom:1px solid #edf3f7}.ap-card-head h2{font-size:1.05rem;font-weight:800;color:var(--ap-ink);margin:0}.ap-card-head p{margin:5px 0 0;color:var(--ap-muted);font-size:.82rem}.ap-card-body{padding:20px}.ap-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.ap-field.full{grid-column:1/-1}.ap-label{display:block;color:#466278;font-size:.75rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px}.ap-field .form-control,.ap-field .form-select{min-height:44px;border:1px solid #d8e4ed;border-radius:10px;color:#233e56}.ap-field textarea{min-height:92px;resize:vertical}.ap-help{font-size:.74rem;color:#7d8e9d;line-height:1.45;margin-top:5px}.ap-submit{margin-top:18px;min-height:48px;border-radius:10px;font-weight:800}.ap-summary{padding:20px;background:linear-gradient(145deg,#f3f8fd,#fff);border-bottom:1px solid var(--ap-line)}.ap-summary h3{font-weight:800;color:#174467;font-size:.98rem;margin:0 0 7px}.ap-summary p{font-size:.8rem;color:var(--ap-muted);line-height:1.55;margin:0}.ap-checklist{padding:18px 20px}.ap-checklist h3{font-size:.9rem;color:#294e69;font-weight:800;margin:0 0 12px}.ap-checklist div{display:flex;gap:9px;align-items:flex-start;font-size:.8rem;color:#60778a;margin:10px 0}.ap-checklist i{color:#1e8a61;margin-top:2px}.ap-result{margin-top:16px;border:1px solid #bce5d2;border-radius:16px;background:#f1fcf6;padding:18px}.ap-result h3{color:#147448;font-size:1rem;font-weight:800;margin:0}.ap-result p{font-size:.82rem;color:#4d6e5e;margin:5px 0 15px}.ap-result-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.ap-result-item{background:#fff;border:1px solid #d9eee3;border-radius:10px;padding:11px}.ap-result-item small{display:block;color:#789184;font-size:.68rem;text-transform:uppercase;font-weight:800}.ap-result-item strong{display:block;color:#1d4839;font-size:.9rem;margin-top:3px;overflow-wrap:anywhere}.ap-pay{margin-top:14px}.ap-pay button{min-height:45px;border-radius:10px;font-weight:800}.ap-alert{border-radius:11px;font-size:.82rem}.ap-rules{font-size:.77rem;color:#738799;line-height:1.55}.ap-rules strong{color:#3c5d75}@media(max-width:820px){.ap-layout{grid-template-columns:1fr}.ap-summary{order:-1}}@media(max-width:575px){.admin-payment-page{padding:10px 8px 28px}.ap-hero{padding:21px 18px;border-radius:16px}.ap-hero .btn{width:100%;margin-top:15px}.ap-card-body{padding:15px}.ap-card-head{padding:15px}.ap-form-grid{grid-template-columns:1fr;gap:11px}.ap-field.full{grid-column:auto}.ap-result-grid{grid-template-columns:1fr 1fr}.ap-result-item:last-child{grid-column:1/-1}.ap-submit{width:100%}}
</style>

<div class="admin-payment-page">
    <div class="admin-payment-wrap">
        <section class="ap-hero">
            <p class="ap-kicker"><i class="fas fa-shield-alt mr-1"></i> Finance workspace</p>
            <div class="d-flex justify-content-between align-items-end flex-wrap" style="gap:14px">
                <div><h1>Make a payment</h1><p>Create a secure Remita invoice for a payer directly from the fees collection workspace. The selected service’s configured split rules are applied automatically.</p></div>
                <a href="{{ url('/fees due') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Back to fees</a>
            </div>
        </section>

        @if(session('success'))<div class="alert alert-success ap-alert mt-3"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger ap-alert mt-3"><i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger ap-alert mt-3"><strong>Please review the payment details.</strong><ul class="mb-0 mt-1 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        @if(is_array($paymentResult))
            <section class="ap-result">
                <h3><i class="fas fa-file-invoice-dollar mr-1"></i> Invoice ready for payment</h3>
                <p>Give the payer the RRR below or open the secure PayNow widget on their behalf.</p>
                <div class="ap-result-grid">
                    <div class="ap-result-item"><small>Payer</small><strong>{{ $paymentResult['payer_name'] ?? '—' }}</strong></div>
                    <div class="ap-result-item"><small>Service</small><strong>{{ $paymentResult['service_label'] ?? '—' }}</strong></div>
                    <div class="ap-result-item"><small>Description</small><strong>{{ $paymentResult['description'] ?? '—' }}</strong></div>
                    <div class="ap-result-item"><small>RRR</small><strong>{{ $paymentResult['rrr'] ?? '—' }}</strong></div>
                    <div class="ap-result-item"><small>Total to pay</small><strong>₦{{ number_format((float) ($paymentResult['total_amount'] ?? 0), 2) }}</strong></div>
                    <div class="ap-result-item"><small>Order ID</small><strong>{{ $paymentResult['order_id'] ?? '—' }}</strong></div>
                </div>
                <div class="ap-pay"><button type="button" class="btn btn-success js-admin-paynow" data-rrr="{{ $paymentResult['rrr'] ?? '' }}"><i class="fas fa-credit-card mr-1"></i> Open Remita PayNow</button></div>
            </section>
        @endif

        <div class="ap-layout">
            <section class="ap-card">
                <div class="ap-card-head"><h2>Payer and payment details</h2><p>Enter the information exactly as it should appear on the Remita invoice.</p></div>
                <div class="ap-card-body">
                    <form method="POST" action="{{ route('admin.make-payment.create') }}" id="adminPaymentForm">
                        @csrf
                        <div class="ap-form-grid">
                            <div class="ap-field"><label class="ap-label" for="payer_name">Payer name <span class="text-danger">*</span></label><input id="payer_name" name="payer_name" class="form-control" value="{{ old('payer_name') }}" required placeholder="Full name of the payer"></div>
                            <div class="ap-field"><label class="ap-label" for="service_type">Payment service <span class="text-danger">*</span></label><select id="service_type" name="service_type" class="form-select" required><option value="">Select service</option>@foreach($services as $service)<option value="{{ $service['key'] }}" data-amount="{{ $service['amount'] ?? 0 }}" data-description="{{ $service['label'] ?? $service['key'] }}" {{ old('service_type') === $service['key'] ? 'selected' : '' }}>{{ $service['label'] ?? $service['key'] }}@if((float) ($service['amount'] ?? 0) > 0) — ₦{{ number_format((float) $service['amount'], 2) }}@endif</option>@endforeach</select><div class="ap-help">The service type determines the Remita service ID and any configured additional charge.</div></div>
                            <div class="ap-field"><label class="ap-label" for="email">Email address <span class="text-danger">*</span></label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required placeholder="payer@example.com"></div>
                            <div class="ap-field"><label class="ap-label" for="phone">Phone number <span class="text-danger">*</span></label><input id="phone" name="phone" type="tel" class="form-control" value="{{ old('phone') }}" required placeholder="08012345678"></div>
                            <div class="ap-field"><label class="ap-label" for="amount">Amount <span class="text-danger">*</span></label><input id="amount" name="amount" type="number" class="form-control" min="1" step="0.01" value="{{ old('amount') }}" required placeholder="0.00"><div class="ap-help">Enter the base amount. Any configured service charge is added securely during invoice preparation.</div></div>
                            <div class="ap-field"><label class="ap-label" for="session">Academic session <span class="text-danger">*</span></label><select id="session" name="session" class="form-select" required>@foreach($sessions as $sessionRow)<option value="{{ $sessionRow->title }}" {{ old('session', $currentPaymentSession) === $sessionRow->title ? 'selected' : '' }}>{{ $sessionRow->title }}</option>@endforeach</select></div>
                            <div class="ap-field full"><label class="ap-label" for="description">Payment description <span class="text-danger">*</span></label><textarea id="description" name="description" class="form-control" required maxlength="180" placeholder="What is this payment for?">{{ old('description') }}</textarea><div class="ap-help">This description appears on the invoice and in the payment records.</div></div>
                        </div>
                        <button type="submit" class="btn btn-primary ap-submit" id="adminPaymentSubmit"><i class="fas fa-file-invoice-dollar mr-1"></i> Create payment invoice</button>
                    </form>
                </div>
            </section>
            <aside class="ap-card">
                <div class="ap-summary"><h3><i class="fas fa-lock mr-1"></i> Secure collection</h3><p>This workflow uses the same Remita preparation and invoice transaction used by student payments.</p></div>
                <div class="ap-checklist"><h3>Before creating the invoice</h3><div><i class="fas fa-check-circle"></i><span>Confirm the payer’s email and phone number.</span></div><div><i class="fas fa-check-circle"></i><span>Select the service that matches the payment.</span></div><div><i class="fas fa-check-circle"></i><span>Enter the base amount to collect.</span></div><div><i class="fas fa-check-circle"></i><span>Review the description before submitting.</span></div><p class="ap-rules mt-3 mb-0"><strong>Important:</strong> Creating the invoice does not mark it as paid. It remains pending until Remita confirms payment.</p></div>
            </aside>
        </div>
    </div>
</div>

<script src="{{ \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl() }}/payment/v1/remita-pay-inline.bundle.js"></script>
<script>
(function(){
    const service = document.getElementById('service_type');
    const amount = document.getElementById('amount');
    const description = document.getElementById('description');
    if(service){ service.addEventListener('change', function(){ const option=this.options[this.selectedIndex]; const configured=parseFloat(option?.dataset.amount || '0'); if(configured>0 && (!amount.value || Number(amount.value)===0)) amount.value=configured.toFixed(2); if(option?.dataset.description && !description.value) description.value=option.dataset.description; }); }
    const form=document.getElementById('adminPaymentForm'); const submit=document.getElementById('adminPaymentSubmit'); if(form&&submit){ form.addEventListener('submit', function(){ submit.disabled=true; submit.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i> Creating invoice...'; }); }
    const payButton=document.querySelector('.js-admin-paynow'); if(payButton){ payButton.addEventListener('click', async function(){ const rrr=this.dataset.rrr; const original=this.innerHTML; this.disabled=true; this.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i> Opening Remita...'; try{ const started=Date.now(); while(typeof window.RmPaymentEngine==='undefined' && Date.now()-started<15000){ await new Promise(resolve=>setTimeout(resolve,150)); } if(typeof window.RmPaymentEngine==='undefined') throw new Error('The Remita payment service is taking too long to load.'); const engine=window.RmPaymentEngine.init({key:@json(config('services.remita.public_key')),processRrr:true,channels:'card,bank,branch,ussd,qr,ibank,paywithremita,buyoncredit,wallet,phonenumber,transfer,enaira',transactionId:'UG-ADMIN-'+Date.now(),extendedData:{customFields:[{name:'rrr',value:rrr}]},onSuccess:function(){window.location.href='{{ url('/verify') }}?rrr='+encodeURIComponent(rrr);},onError:function(response){payButton.disabled=false;payButton.innerHTML=original;alert((response&&(response.message||response.error))||'Payment was not completed.');},onClose:function(){payButton.disabled=false;payButton.innerHTML=original;}}); engine.showPaymentWidget(); }catch(error){ this.disabled=false; this.innerHTML=original; alert(error.message||'The Remita payment service is unavailable.'); } }); }
})();
</script>
