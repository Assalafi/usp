<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm your NIN | UNIMAID Portal</title>
    <style>
        :root{--brand:#3ea1e4;--brand-dark:#1769aa;--ink:#17324d;--muted:#62768a;--line:#dfeaf2;--danger:#a92f38}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:18px;background:linear-gradient(145deg,#eef8fe,#f8fbfd 58%,#eaf1f6);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink)}
        .card{width:min(100%,560px);overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 24px 70px rgba(24,72,105,.15)}
        .head{padding:27px 28px 25px;color:#fff;background:linear-gradient(135deg,var(--brand-dark),var(--brand))}.brand{display:flex;align-items:center;gap:11px;margin-bottom:25px;font-size:.78rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.brand img{width:43px;height:43px;padding:5px;object-fit:contain;background:#fff;border-radius:12px}.head h1{margin:0;font-size:clamp(1.35rem,4vw,1.85rem);line-height:1.2}.head p{margin:9px 0 0;color:rgba(255,255,255,.88);font-size:.92rem;line-height:1.55}.body{padding:27px 28px 30px}.welcome{display:flex;gap:12px;align-items:flex-start;padding:14px 15px;margin-bottom:20px;border:1px solid #d6ebf8;border-radius:14px;background:#f1f9fe;color:#2d5a73;font-size:.88rem;line-height:1.55}.welcome strong{display:block;color:var(--ink);margin-bottom:2px}.icon{display:grid;place-items:center;flex:0 0 auto;width:32px;height:32px;border-radius:10px;background:#d9effc;color:var(--brand-dark)}label{display:block;margin-bottom:8px;font-size:.85rem;font-weight:800;color:var(--ink)}input{width:100%;height:52px;padding:0 15px;border:1px solid #cbdde8;border-radius:13px;color:var(--ink);font-size:1.08rem;letter-spacing:.13em;outline:none;transition:border .15s,box-shadow .15s}input:focus{border-color:var(--brand);box-shadow:0 0 0 4px rgba(62,161,228,.15)}small{display:block;margin-top:8px;color:var(--muted);font-size:.76rem;line-height:1.5}.error{margin:0 0 16px;padding:12px 14px;border:1px solid #f0c9cc;border-radius:12px;background:#fff4f4;color:#8d2630;font-size:.82rem;line-height:1.5}.button-row{display:flex;gap:10px;margin-top:22px}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:47px;padding:0 18px;border-radius:12px;font-size:.86rem;font-weight:800;text-decoration:none;cursor:pointer}.primary{flex:1;border:0;color:#fff;background:var(--brand-dark)}.primary:hover{background:#0f578f}.logout{border:1px solid #e2cacc;color:var(--danger);background:#fff}.foot{margin-top:18px;padding-top:17px;border-top:1px solid #edf2f5;color:#7a8b9a;font-size:.75rem;line-height:1.5;text-align:center}@media(max-width:520px){body{padding:10px}.card{border-radius:18px}.head,.body{padding:22px 19px}.brand{margin-bottom:21px}.button-row{flex-direction:column}.logout{order:2}}
    </style>
</head>
<body>
    <main class="card" aria-labelledby="nin-title">
        <section class="head">
            <div class="brand"><img src="{{ asset('uploads/logo-transparent.png') }}" alt="University of Maiduguri crest"><span>University of Maiduguri Portal</span></div>
            <h1 id="nin-title">Confirm your National Identification Number</h1>
            <p>This security check helps protect your student account and keep your records accurate.</p>
        </section>
        <section class="body">
            @if(session('error'))<div class="error">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="error">{{ $errors->first('nin') }}</div>@endif
            <div class="welcome"><span class="icon">🔒</span><div><strong>{{ $student->fullname ?? 'Student' }}</strong>Enter your NIN to continue. The portal may ask you to confirm it again when the University starts a new security review.</div></div>
            <form method="POST" action="{{ route('student.nin.update.submit') }}" autocomplete="off">
                @csrf
                <label for="nin">11-digit NIN</label>
                <input id="nin" name="nin" type="text" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" minlength="11" value="{{ old('nin') }}" required autofocus aria-describedby="nin-help">
                <small id="nin-help">Use only the 11 digits on your National Identification Number slip. Your NIN is protected and cannot be changed by another student.</small>
                <div class="button-row"><button class="btn primary" type="submit">Save NIN and continue</button><a class="btn logout" href="{{ url('/logout') }}">Log out</a></div>
            </form>
            <div class="foot">You must complete this security check before accessing payments, results, registration, or other portal services.</div>
        </section>
    </main>
</body>
</html>
