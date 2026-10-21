<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Record Update Deadline Expired</title>
    <style>
        :root {
            --brand: #3ea1e4;
            --ink: #172b4d;
            --muted: #5b6b82;
            --danger: #b42318;
            --danger-soft: #fff1f0;
            --line: #e4eaf1;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px 16px;
            color: var(--ink);
            background: linear-gradient(145deg, #eef7fd 0%, #f8fbfe 54%, #edf2f8 100%);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .notice {
            width: min(100%, 760px);
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 22px 60px rgba(29, 67, 98, .14);
        }
        .notice__top {
            padding: 28px 30px 24px;
            color: #fff;
            background: linear-gradient(135deg, #237fb7, var(--brand));
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 26px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .brand img {
            width: 42px;
            height: 42px;
            padding: 5px;
            object-fit: contain;
            background: #fff;
            border-radius: 12px;
        }
        h1 {
            margin: 0;
            font-size: clamp(1.35rem, 4vw, 2rem);
            line-height: 1.18;
            letter-spacing: -.02em;
        }
        .notice__body { padding: 30px; }
        .notice__body h2 {
            margin: 0 0 18px;
            color: var(--danger);
            font-size: clamp(1.05rem, 3vw, 1.3rem);
            line-height: 1.3;
        }
        .notice__body p {
            margin: 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.75;
        }
        .restriction {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-top: 24px;
            padding: 15px 16px;
            color: #7a271a;
            background: var(--danger-soft);
            border: 1px solid #f3c5c0;
            border-radius: 14px;
            font-size: .94rem;
            line-height: 1.55;
        }
        .restriction strong { display: block; margin-bottom: 3px; }
        .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
        }
        .logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            color: #fff;
            background: var(--danger);
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: transform .15s ease, background .15s ease;
        }
        .logout:hover { color: #fff; background: #8f1d14; transform: translateY(-1px); }
        @media (max-width: 520px) {
            body { padding: 12px; }
            .notice { border-radius: 18px; }
            .notice__top, .notice__body { padding: 22px 20px; }
            .brand { margin-bottom: 22px; }
            .notice__body p { font-size: .96rem; line-height: 1.65; }
            .actions, .logout { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="notice" aria-labelledby="notice-title">
        <section class="notice__top">
            <div class="brand">
                <img src="{{ asset('uploads/logo-transparent.png') }}" alt="University of Maiduguri crest">
                <span>University of Maiduguri Portal</span>
            </div>
            <h1 id="notice-title">EXPIRATION OF STUDENT RECORD UPDATE DEADLINE</h1>
        </section>

        <section class="notice__body">
            <h2>NOTICE OF RESTRICTED PORTAL ACCESS</h2>
            <p>
                This is to officially inform all students that the final deadline for the online student record update has officially expired. As previously communicated, no further extensions or online grace periods will be granted.
            </p>
            <p style="margin-top: 16px;">
                Consequently, your student portal access has been restricted due to non-compliance. You can no longer update your profile or access your portal dashboard online.
            </p>

            <div class="restriction" role="status">
                <span aria-hidden="true">🔒</span>
                <div>
                    <strong>Access restricted</strong>
                    Only the logout option is available from this page.
                </div>
            </div>

            <div class="actions">
                <a class="logout" href="{{ url('/logout') }}">Log out</a>
            </div>
        </section>
    </main>
</body>
</html>
