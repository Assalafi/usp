<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni sign in | University of Maiduguri</title>
    <link rel="icon" href="{{ asset('uploads/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('dashboard/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/bootstrap/css/bootstrap.min.css') }}">
    <style>
        :root { --alumni-blue:#0b66c3; --alumni-ink:#10243e; --alumni-muted:#64748b; --alumni-soft:#eef6ff; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--alumni-ink); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; background:#f5f8fc; }
        .alumni-login-shell { min-height:100vh; display:grid; grid-template-columns:minmax(0,1.1fr) minmax(380px,.9fr); }
        .alumni-login-hero { position:relative; overflow:hidden; display:flex; align-items:center; padding:clamp(32px,7vw,96px); color:#fff; background:linear-gradient(145deg,#0755a2 0%,#0b82ce 58%,#24a4df 100%); }
        .alumni-login-hero:before,.alumni-login-hero:after { content:""; position:absolute; border:1px solid rgba(255,255,255,.14); border-radius:50%; pointer-events:none; }
        .alumni-login-hero:before { width:560px;height:560px;right:-230px;top:-150px; }
        .alumni-login-hero:after { width:380px;height:380px;left:-180px;bottom:-180px; }
        .hero-content { position:relative; z-index:1; max-width:620px; }
        .brand-lockup { display:flex; align-items:center; gap:14px; margin-bottom:48px; }
        .brand-lockup img { width:58px;height:58px;object-fit:contain;background:#fff;border-radius:16px;padding:8px;box-shadow:0 10px 30px rgba(2,34,76,.2); }
        .brand-lockup strong { display:block; font-size:1.05rem; letter-spacing:.03em; }
        .brand-lockup span { display:block; opacity:.75; font-size:.78rem; margin-top:3px; }
        .hero-content h1 { font-size:clamp(2.1rem,4vw,4.1rem); line-height:1.06; letter-spacing:-.04em; margin:0 0 18px; font-weight:800; }
        .hero-content p { max-width:500px; font-size:1.05rem; line-height:1.7; color:rgba(255,255,255,.84); margin:0; }
        .hero-points { display:flex; flex-wrap:wrap; gap:10px; margin-top:32px; }
        .hero-point { display:inline-flex; align-items:center; gap:8px; padding:9px 12px; border-radius:999px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.16); font-size:.78rem; }
        .hero-point i { color:#b9edff; }
        .alumni-login-panel { display:flex; align-items:center; justify-content:center; padding:clamp(22px,5vw,70px); background:#fff; }
        .login-card { width:min(100%,440px); }
        .mobile-brand { display:none; }
        .login-kicker { display:inline-flex; align-items:center; gap:8px; color:var(--alumni-blue); font-size:.75rem; letter-spacing:.12em; font-weight:800; text-transform:uppercase; }
        .login-card h2 { margin:13px 0 8px; font-size:clamp(1.7rem,3vw,2.2rem); letter-spacing:-.03em; }
        .login-card .intro { color:var(--alumni-muted); line-height:1.6; margin-bottom:26px; }
        .form-label { color:#233a56; font-size:.82rem; font-weight:700; margin-bottom:7px; }
        .input-wrap { position:relative; }
        .input-wrap > i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#8ba0b7; pointer-events:none; }
        .input-wrap .form-control { min-height:52px; padding-left:44px; padding-right:44px; border:1px solid #dce6f1; border-radius:13px; box-shadow:none; }
        .input-wrap .form-control:focus { border-color:#4a9ce0; box-shadow:0 0 0 4px rgba(11,102,195,.1); }
        .password-toggle { position:absolute; right:10px; top:50%; transform:translateY(-50%); border:0; background:transparent; color:#7b91a8; padding:7px; }
        .login-submit { width:100%; min-height:52px; border:0; border-radius:13px; color:#fff; background:linear-gradient(135deg,#075bab,#087fca); font-weight:800; box-shadow:0 10px 20px rgba(8,104,183,.2); transition:transform .2s,box-shadow .2s; }
        .login-submit:hover { color:#fff; transform:translateY(-1px); box-shadow:0 14px 26px rgba(8,104,183,.28); }
        .login-help { display:flex; gap:10px; align-items:flex-start; margin-top:22px; padding:13px 14px; border-radius:12px; background:var(--alumni-soft); color:#49627e; font-size:.78rem; line-height:1.5; }
        .login-help i { color:var(--alumni-blue); margin-top:2px; }
        .back-link { display:inline-flex; align-items:center; gap:7px; margin-top:25px; color:#526b86; font-size:.82rem; text-decoration:none; }
        .back-link:hover { color:var(--alumni-blue); }
        .alert { border-radius:12px; border:0; font-size:.83rem; }
        .footer-note { margin-top:34px; color:#91a0b0; font-size:.72rem; text-align:center; }
        @media (max-width: 820px) {
            .alumni-login-shell { display:block; }
            .alumni-login-hero { min-height:310px; padding:28px 22px 42px; align-items:flex-end; }
            .brand-lockup { margin-bottom:30px; }
            .hero-content h1 { font-size:2.3rem; }
            .hero-content p { font-size:.9rem; line-height:1.55; }
            .hero-points { margin-top:20px; gap:7px; }
            .hero-point { font-size:.68rem; padding:7px 9px; }
            .alumni-login-panel { padding:28px 18px 34px; margin-top:-18px; border-radius:22px 22px 0 0; position:relative; }
            .login-card { width:100%; }
        }
        @media (max-width: 420px) {
            .alumni-login-hero { min-height:285px; }
            .brand-lockup img { width:48px;height:48px;border-radius:13px;padding:6px; }
            .brand-lockup strong { font-size:.9rem; }
            .hero-content h1 { font-size:2rem; }
            .hero-points .hero-point:nth-child(3) { display:none; }
        }
    </style>
</head>
<body>
    <main class="alumni-login-shell">
        <section class="alumni-login-hero" aria-label="Alumni portal introduction">
            <div class="hero-content">
                <div class="brand-lockup">
                    <img src="{{ asset('uploads/logo.png') }}" alt="University of Maiduguri logo">
                    <div><strong>UNIVERSITY OF MAIDUGURI</strong><span>Alumni portal</span></div>
                </div>
                <h1>Stay connected to your University.</h1>
                <p>Access approved services, make secure payments and keep your University of Maiduguri Alumni account within reach.</p>
                <div class="hero-points" aria-label="Alumni portal features">
                    <span class="hero-point"><i class="fas fa-credit-card"></i> Secure payments</span>
                    <span class="hero-point"><i class="fas fa-receipt"></i> Digital receipts</span>
                    <span class="hero-point"><i class="fas fa-shield-alt"></i> Protected access</span>
                </div>
            </div>
        </section>
        <section class="alumni-login-panel">
            <div class="login-card">
                <span class="login-kicker"><i class="fas fa-user-graduate"></i> Alumni sign in</span>
                <h2>Welcome back</h2>
                <p class="intro">Sign in with your Alumni username or ID number to continue.</p>

                @if (session('error'))
                    <div class="alert alert-danger mb-3"><i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('alumni.auth') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="alumniUsername">Username or Alumni ID</label>
                        <div class="input-wrap"><i class="fas fa-id-card"></i><input id="alumniUsername" type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" autocomplete="username" placeholder="Enter your username or ID" required autofocus></div>
                        @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="alumniPassword">Password</label>
                        <div class="input-wrap"><i class="fas fa-lock"></i><input id="alumniPassword" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" placeholder="Enter your password" required><button type="button" class="password-toggle" id="toggleAlumniPassword" aria-label="Show password"><i class="fas fa-eye"></i></button></div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="login-submit"><i class="fas fa-arrow-right-to-bracket me-2"></i>Sign in to Alumni portal</button>
                </form>

                <div class="login-help"><i class="fas fa-circle-info"></i><span>Alumni accounts are created or activated by the University. If your details are not accepted, contact the Alumni or Accounts office.</span></div>
                <a href="{{ url('/') }}" class="back-link"><i class="fas fa-arrow-left"></i>Back to student portal login</a>
                <div class="footer-note">University of Maiduguri · Alumni services</div>
            </div>
        </section>
    </main>
    <script>
        (function () {
            const input = document.getElementById('alumniPassword');
            const toggle = document.getElementById('toggleAlumniPassword');
            if (!input || !toggle) return;
            toggle.addEventListener('click', function () {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
                const icon = toggle.querySelector('i');
                if (icon) icon.className = visible ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        })();
    </script>
</body>
</html>
