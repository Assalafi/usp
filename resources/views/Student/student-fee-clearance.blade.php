<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clear outstanding fees | UNIMAID Portal</title>
    <style>
        :root{--brand:#257fba;--brand2:#48a9e7;--ink:#17324d;--muted:#607589;--line:#dce8f0;--danger:#9c2f37;--danger-bg:#fff3f3;--gold:#a66a00;--gold-bg:#fff8e8}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:16px;background:linear-gradient(145deg,#eef8fe,#f9fbfd 58%,#eaf1f6);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
        .card{width:min(100%,640px);overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 24px 70px rgba(24,72,105,.16)}
        .head{padding:27px 29px 26px;color:#fff;background:linear-gradient(135deg,var(--brand),var(--brand2))}.brand{display:flex;align-items:center;gap:11px;margin-bottom:24px;font-size:.78rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.brand img{width:43px;height:43px;padding:5px;object-fit:contain;background:#fff;border-radius:12px}.head h1{margin:0;font-size:clamp(1.35rem,4vw,1.9rem);line-height:1.2}.head p{margin:9px 0 0;color:rgba(255,255,255,.9);font-size:.93rem;line-height:1.55}.body{padding:28px 29px 30px}.notice{display:flex;gap:12px;align-items:flex-start;margin-bottom:20px;padding:15px 16px;border:1px solid #f0c8c9;border-radius:14px;background:var(--danger-bg);color:#7d2730;font-size:.91rem;line-height:1.55}.notice strong{display:block;margin-bottom:3px;color:var(--danger)}.icon{display:grid;place-items:center;flex:0 0 auto;width:34px;height:34px;border-radius:10px;background:#f8dfe0}.person{margin-bottom:20px;color:var(--muted);font-size:.89rem}.person strong{display:block;color:var(--ink);font-size:1rem}.summary{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0}.summary div{padding:15px;border:1px solid var(--line);border-radius:14px;background:#f8fbfd}.summary small{display:block;margin-bottom:6px;color:var(--muted);font-size:.76rem}.summary strong{display:block;color:var(--ink);font-size:1.12rem}.summary .balance{border-color:#f0d9a5;background:var(--gold-bg)}.summary .balance strong{color:var(--gold)}.copy{margin:0;color:var(--muted);font-size:.91rem;line-height:1.7}.actions{display:flex;gap:10px;margin-top:24px}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 19px;border-radius:12px;font-size:.88rem;font-weight:800;text-decoration:none}.primary{flex:1;color:#fff;background:#1769aa}.primary:hover{color:#fff;background:#115889}.logout{color:var(--danger);border:1px solid #e4c6c8;background:#fff}.foot{margin-top:20px;padding-top:17px;border-top:1px solid #edf2f5;color:#7b8d9b;font-size:.76rem;line-height:1.5;text-align:center}@media(max-width:520px){body{padding:10px}.card{border-radius:18px}.head,.body{padding:22px 19px}.summary{grid-template-columns:1fr}.actions{flex-direction:column}.logout{order:2}.primary,.logout{width:100%}}
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
            <div class="notice" role="alert"><span class="icon">!</span><div><strong>Portal access is temporarily restricted</strong>Please settle the complete outstanding balance below. Until it is paid, only this payment page and logout are available.</div></div>
            <div class="person"><strong>{{ $student->fullname }}</strong>{{ $student->username }}</div>
            <div class="summary">
                <div><small>Recovery session</small><strong>{{ $balance['session'] }}</strong></div>
                <div class="balance"><small>Amount to clear</small><strong>₦{{ number_format((float) $balance['balance'], 2) }}</strong></div>
            </div>
            <p class="copy">The amount shown includes the programme-fee balance recorded from your earlier academic sessions. A full payment is required; part-payment is not available for this clearance.</p>
            <div class="actions"><a class="btn primary" href="{{ url('/payment') }}">Continue to payment</a><a class="btn logout" href="{{ url('/logout') }}">Log out</a></div>
            <div class="foot">After Remita confirms the payment, your normal student portal access will be restored automatically.</div>
        </section>
    </main>
</body>
</html>
