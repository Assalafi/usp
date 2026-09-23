<style>
    .ug-pin-page {
        --ug-blue: #3ea1e4;
        --ug-blue-dark: #1475b6;
        --ug-ink: #18354f;
        --ug-muted: #667d90;
        --ug-line: #e2edf4;
        max-width: 980px;
        margin: 0 auto;
        padding: 1.25rem 1rem 3rem;
        color: var(--ug-ink);
    }
    .ug-pin-page *, .ug-pin-page *::before, .ug-pin-page *::after { box-sizing: border-box; }
    .ug-pin-hero {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        padding: clamp(1.4rem, 4vw, 2.5rem);
        background: linear-gradient(120deg, #116eae 0%, var(--ug-blue) 60%, #74c6f2 100%);
        color: #fff;
        box-shadow: 0 18px 42px rgba(34, 117, 170, .18);
    }
    .ug-pin-hero::after { content: ''; position: absolute; width: 260px; height: 260px; right: -110px; top: -135px; border-radius: 50%; background: rgba(255,255,255,.13); }
    .ug-pin-hero__content { position: relative; z-index: 1; max-width: 680px; }
    .ug-pin-eyebrow { display: inline-flex; align-items: center; gap: .45rem; margin-bottom: .65rem; font-size: .75rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; opacity: .88; }
    .ug-pin-hero h1 { margin: 0 0 .55rem; color: #fff; font-size: clamp(1.55rem, 3vw, 2.25rem); font-weight: 800; }
    .ug-pin-hero p { margin: 0; color: rgba(255,255,255,.9); font-size: .98rem; line-height: 1.65; }
    .ug-pin-layout { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(260px, .85fr); gap: 1rem; align-items: start; margin-top: 1rem; }
    .ug-pin-card { border: 1px solid var(--ug-line); border-radius: 18px; background: #fff; box-shadow: 0 8px 28px rgba(31, 78, 112, .07); }
    .ug-pin-card__head { padding: 1.3rem 1.35rem 0; }
    .ug-pin-card__head h2, .ug-pin-card__head h3 { margin: 0; color: var(--ug-ink); font-size: 1.1rem; font-weight: 800; }
    .ug-pin-card__head p { margin: .35rem 0 0; color: var(--ug-muted); font-size: .86rem; line-height: 1.55; }
    .ug-pin-card__body { padding: 1.25rem 1.35rem 1.35rem; }
    .ug-pin-field { margin-bottom: 1rem; }
    .ug-pin-field label { display: block; margin-bottom: .42rem; color: #3d5870; font-size: .8rem; font-weight: 700; }
    .ug-pin-field input, .ug-pin-field select { width: 100%; min-height: 49px; border: 1px solid #d6e5ee; border-radius: 11px; background: #fff; color: var(--ug-ink); padding: .7rem .8rem; outline: none; transition: border-color .2s, box-shadow .2s; }
    .ug-pin-field input:focus, .ug-pin-field select:focus { border-color: var(--ug-blue); box-shadow: 0 0 0 3px rgba(62,161,228,.14); }
    .ug-pin-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; min-height: 47px; border: 0; border-radius: 11px; background: var(--ug-blue); color: #fff; padding: .75rem 1rem; font-weight: 800; box-shadow: 0 7px 17px rgba(62,161,228,.24); cursor: pointer; transition: transform .2s, opacity .2s; }
    .ug-pin-button:hover { transform: translateY(-1px); }
    .ug-pin-button:disabled { cursor: wait; opacity: .72; transform: none; }
    .ug-pin-spinner { display: none; width: 16px; height: 16px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: ug-pin-spin .7s linear infinite; }
    .is-loading .ug-pin-spinner { display: inline-block; }
    @keyframes ug-pin-spin { to { transform: rotate(360deg); } }
    .ug-pin-list { display: grid; gap: .75rem; margin: 0; padding: 0; list-style: none; }
    .ug-pin-list li { display: flex; align-items: flex-start; gap: .6rem; color: #536b7d; font-size: .84rem; line-height: 1.55; }
    .ug-pin-list i { margin-top: .18rem; color: var(--ug-blue); }
    .ug-pin-note { margin-top: 1rem; border: 1px solid #f3dfae; border-radius: 13px; padding: .85rem .95rem; background: #fffaf0; color: #785c19; font-size: .8rem; line-height: 1.55; }
    .ug-pin-success { text-align: center; }
    .ug-pin-success__icon { display: grid; width: 62px; height: 62px; margin: 0 auto .85rem; place-items: center; border-radius: 50%; background: #e7f8ee; color: #1d9551; font-size: 1.5rem; }
    .ug-pin-success h2 { margin: 0 0 .4rem; color: var(--ug-ink); font-size: 1.35rem; }
    .ug-pin-success p { margin: 0 auto; color: var(--ug-muted); font-size: .88rem; line-height: 1.6; }
    .ug-pin-value { margin: 1rem 0; border: 1px dashed #acd6eb; border-radius: 13px; padding: .85rem; background: #f2faff; color: var(--ug-blue-dark); }
    .ug-pin-value small, .ug-pin-value strong { display: block; }
    .ug-pin-value small { margin-bottom: .2rem; color: #55778b; font-size: .72rem; }
    .ug-pin-value strong { font-size: 1.25rem; letter-spacing: .12em; }
    .ug-pin-multiple { margin: 1rem 0; border: 1px solid #f3dfae; border-radius: 13px; padding: .85rem; background: #fffaf0; color: #785c19; text-align: left; font-size: .8rem; line-height: 1.5; }
    .ug-pin-multiple strong { color: #634b12; }
    .ug-pin-multiple ul { display: flex; flex-wrap: wrap; gap: .45rem; margin: .55rem 0 0; padding: 0; list-style: none; }
    .ug-pin-multiple li { border: 1px solid #ead69f; border-radius: 8px; padding: .35rem .5rem; background: #fff; color: #634b12; font-weight: 800; letter-spacing: .08em; }
    .ug-pin-link { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; width: 100%; min-height: 45px; border-radius: 11px; background: var(--ug-blue); color: #fff !important; font-weight: 800; text-decoration: none !important; }
    .ug-pin-empty { border: 1px dashed #cbdde9; border-radius: 13px; padding: 1rem; background: #f6fafc; color: #526d82; line-height: 1.6; }
    @media (max-width: 800px) { .ug-pin-layout { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .ug-pin-page { padding: .75rem .65rem 2rem; } .ug-pin-hero { border-radius: 17px; padding: 1.25rem 1.05rem; } .ug-pin-card__head { padding: 1.05rem 1rem 0; } .ug-pin-card__body { padding: 1rem; } }
</style>

@php
    $studentId = session('id_number');
    $validatedPin = $hostelPin ?? null;
    $validatedPins = $hostelPins ?? ($validatedPin ? collect([$validatedPin]) : collect());
@endphp

<main class="ug-pin-page" aria-labelledby="ug-pin-title">
    <section class="ug-pin-hero">
        <div class="ug-pin-hero__content">
            <div class="ug-pin-eyebrow"><i class="fas fa-key" aria-hidden="true"></i> Hostel access</div>
            <h1 id="ug-pin-title">Validate your hostel PIN</h1>
            <p>Use the PIN issued to you to unlock hostel bed-space reservations. You only need to validate it once.</p>
        </div>
    </section>

    <div class="ug-pin-layout">
        <section class="ug-pin-card">
            @if (!$studentId)
                <div class="ug-pin-card__head"><h2>ID number required</h2><p>Your student ID number is not available in this login session.</p></div>
                <div class="ug-pin-card__body"><div class="ug-pin-empty"><i class="fas fa-id-card me-1" aria-hidden="true"></i> Sign in again with your student account before validating a hostel PIN.</div></div>
            @elseif ($validatedPin)
                <div class="ug-pin-card__body ug-pin-success">
                    <div class="ug-pin-success__icon"><i class="fas fa-check" aria-hidden="true"></i></div>
                    <h2>PIN already validated</h2>
                    <p>Your hostel PIN is linked to your student account. You can proceed to bed-space reservations.</p>
                    @if ($validatedPins->count() > 1)
                        <div class="ug-pin-multiple"><strong>Multiple PIN records are linked to this account.</strong><br>Do not share them. Contact Student Affairs to confirm which one should remain active.<ul>@foreach ($validatedPins as $linkedPin)<li>{{ $linkedPin->pin }}</li>@endforeach</ul></div>
                    @elseif (!empty($validatedPin->pin))
                        <div class="ug-pin-value"><small>Your hostel PIN</small><strong>{{ $validatedPin->pin }}</strong></div>
                    @endif
                    <a class="ug-pin-link" href="{{ url('bed space reservations') }}"><i class="fas fa-bed" aria-hidden="true"></i> Go to bed-space reservations</a>
                </div>
            @else
                <div class="ug-pin-card__head"><h2>Enter your PIN</h2><p>Enter the PIN exactly as printed and select the gender on your student record.</p></div>
                <div class="ug-pin-card__body">
                    <form method="POST" action="{{ url('V-H-Pin') }}" id="ug-pin-form">
                        @csrf
                        <input type="hidden" name="email" value="{{ $studentId }}">
                        <div class="ug-pin-field"><label for="ug-hostel-pin">Hostel PIN</label><input id="ug-hostel-pin" type="text" name="password" required autocomplete="one-time-code" autocapitalize="characters" spellcheck="false" placeholder="Enter your hostel PIN"></div>
                        <div class="ug-pin-field"><label for="ug-hostel-gender">Gender</label><select id="ug-hostel-gender" name="gender" required><option value="">Select your gender</option><option value="MALE">Male</option><option value="FEMALE">Female</option></select></div>
                        <button type="submit" class="ug-pin-button"><span class="ug-pin-spinner" aria-hidden="true"></span><i class="fas fa-check-circle" aria-hidden="true"></i><span>Validate PIN</span></button>
                    </form>
                </div>
            @endif
        </section>

        <aside class="ug-pin-card">
            <div class="ug-pin-card__head"><h3>How it works</h3><p>Keep your PIN private and use it only on the official portal.</p></div>
            <div class="ug-pin-card__body">
                <ul class="ug-pin-list">
                    <li><i class="fas fa-ticket-alt" aria-hidden="true"></i><span>Use the PIN issued by Student Affairs.</span></li>
                    <li><i class="fas fa-user-check" aria-hidden="true"></i><span>Validation links the PIN to your student ID.</span></li>
                    <li><i class="fas fa-bed" aria-hidden="true"></i><span>After validation, open <strong>Bed Space Reservations</strong> to choose a space.</span></li>
                    <li><i class="fas fa-lock" aria-hidden="true"></i><span>A PIN that has already been used cannot be validated again.</span></li>
                </ul>
                <div class="ug-pin-note"><strong>Need help?</strong><br>Contact Student Affairs if your PIN is invalid, already used, or does not match your record.</div>
            </div>
        </aside>
    </div>
</main>

<script>
    (function () {
        const form = document.getElementById('ug-pin-form');
        if (!form) return;
        form.addEventListener('submit', function () {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.classList.add('is-loading');
            const label = button.querySelector('span:last-child');
            if (label) label.textContent = 'Validating…';
        });
    })();
</script>
