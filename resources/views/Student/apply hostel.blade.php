@php
    $flagO = $flag;

    // Keep the existing eligibility rules, but present their result in a clearer interface.
    function apply($idNumber)
    {
        $duration = session('duration');
        $level = session('current_level');
        $faculty = session('faculty');
        $idPrefix = (int) substr($idNumber, 0, 2);
        $reasons = [];

        if ($duration == 4 && $idPrefix >= 20 && $level < 400) {
            return true;
        }
        if ($duration == 5 && $idPrefix >= 19 && $level < 500) {
            return true;
        }
        if ($duration == 6 && $idPrefix >= 22 && $level < 400) {
            return true;
        }
        if ($duration == 6 && $idPrefix >= 18 && $level < 600 && $faculty == 'VET') {
            return true;
        }

        if ($duration == 4) {
            if ($idPrefix < 20) {
                $reasons[] = 'Your ID number must start with 20 or higher for a 4-year programme.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your level must be below 400 for a 4-year programme.';
            }
        } elseif ($duration == 5) {
            if ($idPrefix < 19) {
                $reasons[] = 'Your ID number must start with 19 or higher for a 5-year programme.';
            }
            if ($level >= 500) {
                $reasons[] = 'Your level must be below 500 for a 5-year programme.';
            }
        } elseif ($duration == 6) {
            if ($idPrefix < 22) {
                $reasons[] = 'Your ID number must start with 22 or higher for a 6-year programme.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your level must be below 400 for a 6-year programme.';
            }
        } else {
            $reasons[] = 'Your programme is not currently eligible for hostel accommodation.';
        }

        return [
            'eligible' => false,
            'message' => 'You cannot apply for hostel accommodation at this time.',
            'reasons' => $reasons,
        ];
    }

    $check = apply(session('id_number'));
    if (isset($check['eligible'])) {
        $flag = $flagO;
    }
    $hostelPins = $hostelPins ?? collect();
    $hostelPin = $hostelPins->first();
    $studentId = session('id_number');
@endphp

<style>
    .ug-hostel-page {
        --ug-blue: #3ea1e4;
        --ug-blue-dark: #1577b9;
        --ug-ink: #16324f;
        --ug-muted: #66788a;
        --ug-line: #e5edf4;
        --ug-soft: #f5f9fc;
        max-width: 1180px;
        margin: 0 auto;
        padding: 1.25rem 1rem 3rem;
        color: var(--ug-ink);
    }

    .ug-hostel-page *, .ug-hostel-page *::before, .ug-hostel-page *::after { box-sizing: border-box; }
    .ug-hostel-hero {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        padding: clamp(1.35rem, 3vw, 2.35rem);
        background: linear-gradient(120deg, #116eae 0%, var(--ug-blue) 58%, #74c6f2 100%);
        color: #fff;
        box-shadow: 0 18px 42px rgba(34, 117, 170, .18);
    }
    .ug-hostel-hero::after {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        right: -100px;
        top: -145px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
    }
    .ug-hostel-hero__content { position: relative; z-index: 1; max-width: 700px; }
    .ug-hostel-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .65rem;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        opacity: .88;
    }
    .ug-hostel-hero h1 { margin: 0 0 .55rem; color: #fff; font-size: clamp(1.55rem, 3vw, 2.35rem); font-weight: 800; }
    .ug-hostel-hero p { margin: 0; max-width: 650px; color: rgba(255,255,255,.9); font-size: .98rem; line-height: 1.65; }
    .ug-hostel-pin { display: inline-flex; align-items: center; gap: .6rem; margin-top: 1rem; border: 1px solid rgba(255,255,255,.28); border-radius: 12px; padding: .55rem .75rem; background: rgba(0,0,0,.13); color: #fff; font-size: .82rem; }
    .ug-hostel-pin i { color: #d8f1ff; }
    .ug-hostel-pin strong { margin-left: .2rem; letter-spacing: .08em; }
    .ug-hostel-pin-warning { display: flex; align-items: flex-start; gap: .45rem; max-width: 620px; margin-top: .55rem; color: #fff1c7; font-size: .76rem; line-height: 1.45; }
    .ug-hostel-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr); gap: 1rem; align-items: start; }
    .ug-hostel-card { border: 1px solid var(--ug-line); border-radius: 18px; background: #fff; box-shadow: 0 8px 28px rgba(31, 78, 112, .07); }
    .ug-hostel-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.3rem 1.35rem 0; }
    .ug-hostel-card__head h2, .ug-hostel-card__head h3 { margin: 0; color: var(--ug-ink); font-size: 1.1rem; font-weight: 800; }
    .ug-hostel-card__head p { margin: .35rem 0 0; color: var(--ug-muted); font-size: .86rem; line-height: 1.5; }
    .ug-hostel-card__body { padding: 1.25rem 1.35rem 1.35rem; }
    .ug-hostel-status { display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap; padding: .4rem .65rem; border-radius: 999px; background: #e9f8ef; color: #18723c; font-size: .72rem; font-weight: 800; }
    .ug-hostel-status--pending { background: #fff5dd; color: #9a6300; }
    .ug-hostel-status--neutral { background: #edf3f8; color: #496276; }
    .ug-hostel-fields { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 1rem; }
    .ug-hostel-field { min-width: 0; }
    .ug-hostel-field label { display: flex; align-items: center; gap: .4rem; margin: 0 0 .4rem; color: #3c5870; font-size: .8rem; font-weight: 700; }
    .ug-hostel-field label span { display: grid; width: 22px; height: 22px; place-items: center; border-radius: 7px; background: #eaf5fc; color: var(--ug-blue-dark); font-size: .7rem; }
    .ug-hostel-field select, .ug-hostel-field input { width: 100%; min-height: 48px; border: 1px solid #d9e6ef; border-radius: 11px; background: #fff; color: var(--ug-ink); padding: .7rem .8rem; outline: none; transition: border-color .2s, box-shadow .2s; }
    .ug-hostel-field select:focus, .ug-hostel-field input:focus { border-color: var(--ug-blue); box-shadow: 0 0 0 3px rgba(62,161,228,.14); }
    .ug-hostel-field select:disabled { cursor: not-allowed; background: #f5f8fa; color: #91a0ac; }
    .ug-hostel-help { margin: .55rem 0 0; color: var(--ug-muted); font-size: .76rem; line-height: 1.5; }
    .ug-hostel-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 46px; border: 0; border-radius: 11px; padding: .72rem 1.1rem; font-weight: 800; cursor: pointer; transition: transform .2s, box-shadow .2s, opacity .2s; }
    .ug-hostel-button:hover { transform: translateY(-1px); }
    .ug-hostel-button:disabled { cursor: wait; opacity: .72; transform: none; }
    .ug-hostel-button--primary { background: var(--ug-blue); color: #fff; box-shadow: 0 7px 17px rgba(62,161,228,.24); }
    .ug-hostel-button--danger { background: #dc4b57; color: #fff; box-shadow: 0 7px 17px rgba(220,75,87,.2); }
    .ug-hostel-button--block { width: 100%; margin-top: 1rem; }
    .ug-hostel-spinner { display: none; width: 16px; height: 16px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: ug-hostel-spin .7s linear infinite; }
    .is-loading .ug-hostel-spinner { display: inline-block; }
    @keyframes ug-hostel-spin { to { transform: rotate(360deg); } }

    .ug-hostel-list { display: grid; gap: .7rem; margin: 0; padding: 0; list-style: none; }
    .ug-hostel-list li { display: flex; gap: .65rem; align-items: flex-start; color: #536a7d; font-size: .84rem; line-height: 1.55; }
    .ug-hostel-list i { width: 18px; margin-top: .15rem; color: var(--ug-blue); }
    .ug-hostel-note { margin-top: 1rem; border: 1px solid #f3dfae; border-radius: 13px; padding: .85rem .95rem; background: #fffaf0; color: #785c19; font-size: .82rem; line-height: 1.55; }
    .ug-hostel-note strong { display: block; margin-bottom: .2rem; color: #634b12; }
    .ug-hostel-announcement { display: flex; align-items: flex-start; gap: .85rem; margin: 1rem 0; border: 1px solid #bfe1f4; border-radius: 16px; padding: 1rem 1.1rem; background: linear-gradient(135deg, #eff9ff, #f8fcff); color: #315a73; box-shadow: 0 7px 20px rgba(35, 128, 180, .06); }
    .ug-hostel-announcement__icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border-radius: 11px; background: #d9f0fc; color: var(--ug-blue-dark); }
    .ug-hostel-announcement strong { display: block; margin-bottom: .25rem; color: var(--ug-ink); font-size: .9rem; }
    .ug-hostel-announcement__body { min-width: 0; font-size: .86rem; line-height: 1.6; white-space: normal; overflow-wrap: anywhere; }
    .ug-hostel-empty { display: flex; align-items: flex-start; gap: .8rem; padding: 1rem; border: 1px dashed #cbdde9; border-radius: 13px; background: var(--ug-soft); color: #526d82; }
    .ug-hostel-empty i { margin-top: .15rem; color: var(--ug-blue); }

    .ug-hostel-reservation { display: grid; grid-template-columns: minmax(0, 1fr) minmax(260px, .85fr); gap: 1rem; }
    .ug-hostel-location { display: grid; grid-template-columns: repeat(3, 1fr); gap: .65rem; margin-top: 1rem; }
    .ug-hostel-location__item { padding: .75rem; border: 1px solid var(--ug-line); border-radius: 12px; background: var(--ug-soft); }
    .ug-hostel-location__item small, .ug-hostel-location__item strong { display: block; }
    .ug-hostel-location__item small { margin-bottom: .2rem; color: var(--ug-muted); font-size: .72rem; }
    .ug-hostel-location__item strong { color: var(--ug-ink); font-size: .88rem; overflow-wrap: anywhere; }
    .ug-hostel-meta { display: grid; gap: .7rem; margin-top: 1rem; }
    .ug-hostel-meta__row { display: flex; justify-content: space-between; gap: 1rem; border-bottom: 1px solid var(--ug-line); padding-bottom: .65rem; color: var(--ug-muted); font-size: .83rem; }
    .ug-hostel-meta__row:last-child { border-bottom: 0; padding-bottom: 0; }
    .ug-hostel-meta__row strong { color: var(--ug-ink); text-align: right; overflow-wrap: anywhere; }
    .ug-hostel-rrr { margin: 1rem 0; border-radius: 13px; padding: .9rem 1rem; background: #eef8fe; color: var(--ug-blue-dark); }
    .ug-hostel-rrr small, .ug-hostel-rrr strong { display: block; }
    .ug-hostel-rrr small { margin-bottom: .25rem; color: #52738a; font-size: .72rem; }
    .ug-hostel-rrr strong { font-size: 1.1rem; letter-spacing: .04em; }
    .ug-hostel-success { text-align: center; padding: 1.5rem 1rem .25rem; }
    .ug-hostel-success__icon { display: grid; width: 56px; height: 56px; margin: 0 auto .8rem; place-items: center; border-radius: 50%; background: #e7f8ee; color: #1d9551; font-size: 1.45rem; }
    .ug-hostel-success h2 { margin: 0 0 .4rem; color: var(--ug-ink); font-size: 1.3rem; }
    .ug-hostel-success p { max-width: 560px; margin: 0 auto; color: var(--ug-muted); line-height: 1.6; }
    .ug-hostel-footer-notices { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 1rem; margin-top: 1rem; }
    .ug-hostel-footer-notices .ug-hostel-note { margin: 0; }
    .ug-hostel-alert { margin-top: 1rem; border-radius: 14px; padding: 1rem; background: #fff4f4; color: #8d3740; }
    .ug-hostel-alert h3 { margin: 0 0 .45rem; color: #7e2931; font-size: 1rem; }
    .ug-hostel-alert ul { margin: .5rem 0 0; padding-left: 1.15rem; }

    @media (max-width: 900px) {
        .ug-hostel-grid, .ug-hostel-reservation { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .ug-hostel-page { padding: .75rem .65rem 2rem; }
        .ug-hostel-hero { border-radius: 17px; padding: 1.25rem 1.05rem; }
        .ug-hostel-card__head { padding: 1.05rem 1rem 0; }
        .ug-hostel-card__body { padding: 1rem; }
        .ug-hostel-fields, .ug-hostel-location, .ug-hostel-footer-notices { grid-template-columns: 1fr; }
        .ug-hostel-location { gap: .5rem; }
        .ug-hostel-location__item { display: flex; align-items: center; justify-content: space-between; gap: .7rem; }
        .ug-hostel-location__item small { margin: 0; }
        .ug-hostel-location__item strong { text-align: right; }
        .ug-hostel-button { width: 100%; }
    }
</style>

<main class="ug-hostel-page" aria-labelledby="hostel-page-title">
    <section class="ug-hostel-hero">
        <div class="ug-hostel-hero__content">
            <div class="ug-hostel-eyebrow"><i class="fas fa-building" aria-hidden="true"></i> Hostel accommodation</div>
            <h1 id="hostel-page-title">Reserve your bed space</h1>
            <p>Choose an available hall, room and bed. Your reservation is only complete after payment and permit verification.</p>
            @if ($hostelPins->isNotEmpty())
                <div class="ug-hostel-pin"><i class="fas fa-key" aria-hidden="true"></i><span>Your hostel PIN{{ $hostelPins->count() > 1 ? 's' : '' }}:
                    @foreach ($hostelPins as $linkedPin)<strong>{{ $linkedPin->pin }}</strong>@if (!$loop->last), @endif @endforeach
                </span></div>
                @if ($hostelPins->count() > 1)
                    <div class="ug-hostel-pin-warning"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i><span>Multiple PIN records are linked to your account. Contact Student Affairs to confirm the active one.</span></div>
                @endif
            @endif
        </div>
    </section>

    @if (trim((string) ($hostelAnnouncement ?? '')) !== '')
        <section class="ug-hostel-announcement" aria-label="Hostel announcement">
            <span class="ug-hostel-announcement__icon"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
            <div class="ug-hostel-announcement__body"><strong>Hostel announcement</strong>{!! nl2br(e($hostelAnnouncement)) !!}</div>
        </section>
    @endif

    @if ($flag == 0)
        <div class="ug-hostel-grid">
            <section class="ug-hostel-card">
                <div class="ug-hostel-card__head">
                    <div><h2>Select your preferred space</h2><p>Make each selection in order to see the next available options.</p></div>
                    <span class="ug-hostel-status"><i class="fas fa-circle" style="font-size:.45rem" aria-hidden="true"></i> Open</span>
                </div>
                <div class="ug-hostel-card__body" id="ug-hostel-form-area">
                    @if (!$hostelPin)
                        <div class="ug-hostel-empty"><i class="fas fa-key" aria-hidden="true"></i><div><strong>Hostel PIN required</strong><br>Validate your hostel PIN before trying to reserve a bed space.</div></div>
                    @elseif (count($hall) === 0)
                        <div class="ug-hostel-empty"><i class="fas fa-bed" aria-hidden="true"></i><div><strong>No spaces are available right now</strong><br>Please check again later or contact Student Affairs for assistance.</div></div>
                    @else
                        <form method="POST" action="{{ url('reserve bed') }}" id="ug-hostel-reservation-form">
                            @csrf
                            <div class="ug-hostel-fields">
                                <div class="ug-hostel-field"><label for="ug-hostel-hall"><span>1</span> Hall</label><select id="ug-hostel-hall" name="hall" required><option value="">Select a hall</option>@foreach ($hall as $availableHall)<option value="{{ $availableHall->hall }}">{{ $availableHall->hall }}</option>@endforeach</select></div>
                                <div class="ug-hostel-field"><label for="ug-hostel-block"><span>2</span> Block</label><select id="ug-hostel-block" name="block" required disabled><option value="">Select a hall first</option></select></div>
                                <div class="ug-hostel-field"><label for="ug-hostel-room"><span>3</span> Room</label><select id="ug-hostel-room" name="room" required disabled><option value="">Select a block first</option></select></div>
                                <div class="ug-hostel-field"><label for="ug-hostel-bed"><span>4</span> Bed</label><select id="ug-hostel-bed" name="bed" required disabled><option value="">Select a room first</option></select></div>
                            </div>
                            <p class="ug-hostel-help" id="ug-hostel-status" role="status" aria-live="polite">Start by selecting a hall.</p>
                            <button type="submit" class="ug-hostel-button ug-hostel-button--primary ug-hostel-button--block" data-loading-label="Reserving…"><span class="ug-hostel-spinner" aria-hidden="true"></span><i class="fas fa-check-circle" aria-hidden="true"></i><span>Reserve this bed</span></button>
                        </form>
                    @endif
                </div>
            </section>
            <aside class="ug-hostel-card">
                <div class="ug-hostel-card__head"><div><h3>Before you reserve</h3><p>A few things to keep in mind.</p></div></div>
                <div class="ug-hostel-card__body">
                    <ul class="ug-hostel-list">
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>You need a valid hostel PIN to reserve a space.</span></li>
                        <li><i class="fas fa-list-ol" aria-hidden="true"></i><span>Choose a hall, block, room and bed in that order.</span></li>
                        <li><i class="fas fa-user-lock" aria-hidden="true"></i><span>A bed space is personal and cannot be sold or transferred.</span></li>
                        <li><i class="fas fa-receipt" aria-hidden="true"></i><span>After reserving, generate your invoice and complete payment promptly.</span></li>
                    </ul>
                </div>
            </aside>
        </div>
    @elseif ($flag == 1)
        @foreach ($data as $row)
            @if ($row->hall != 'BOTs')
                <div class="ug-hostel-reservation">
                    <section class="ug-hostel-card"><div class="ug-hostel-card__head"><div><h2>Your bed space is reserved</h2><p>Review the details below before generating your payment invoice.</p></div><span class="ug-hostel-status"><i class="fas fa-check" aria-hidden="true"></i> Reserved</span></div><div class="ug-hostel-card__body"><div class="ug-hostel-meta"><div class="ug-hostel-meta__row"><span>Reference</span><strong>00{{ $row->id }}</strong></div><div class="ug-hostel-meta__row"><span>Student ID</span><strong>{{ $studentId }}</strong></div></div><div class="ug-hostel-location"><div class="ug-hostel-location__item"><small>Hall</small><strong>{{ $row->hall }}</strong></div><div class="ug-hostel-location__item"><small>Block</small><strong>{{ $row->block }}</strong></div><div class="ug-hostel-location__item"><small>Room / bed</small><strong>{{ $row->room }} / {{ $row->bed }}</strong></div></div></div></section>
                    <section class="ug-hostel-card"><div class="ug-hostel-card__head"><div><h3>Next step: payment</h3><p>Use an active email address and phone number.</p></div><span class="ug-hostel-status ug-hostel-status--pending">Payment pending</span></div><div class="ug-hostel-card__body">@if ($row->payment_method == 'Online')<form action="{{ url('invoices/hostel') }}" method="get" class="ug-hostel-action-form"><input type="hidden" name="page" value="hostel"><div class="ug-hostel-field"><label for="hostel-email">Email address</label><input id="hostel-email" type="email" name="email" autocomplete="email" placeholder="you@example.com" required></div><div class="ug-hostel-field" style="margin-top:.8rem"><label for="hostel-phone">Phone number</label><input id="hostel-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" placeholder="080…" required></div><button type="submit" class="ug-hostel-button ug-hostel-button--primary ug-hostel-button--block" data-loading-label="Generating…"><span class="ug-hostel-spinner" aria-hidden="true"></span><i class="fas fa-file-invoice" aria-hidden="true"></i><span>Generate payment invoice</span></button></form>@else<div class="ug-hostel-empty"><i class="fas fa-building" aria-hidden="true"></i><div><strong>Visit Student Affairs</strong><br>Take this reservation reference to Student Affairs for the next step.</div></div>@endif</div></section>
                </div>
            @else
                <div class="ug-hostel-alert"><h3>Further assistance is required</h3>Report to the Office of the Dean of Students (Student Affairs) with your reservation details.</div>
            @endif
        @endforeach
    @elseif ($flag == 2)
        @foreach ($invoice as $row)
            @foreach ($data as $reservation)
                @if ($reservation->hall != 'BOTs')
                    @php
                        $merchantId = env('REMITA_MERCHANT_ID');
                        $apiKey = env('REMITA_API_KEY');
                        $paymentHash = hash('sha512', $merchantId . $rrr . $apiKey);
                    @endphp
                    <div class="ug-hostel-reservation">
                        <section class="ug-hostel-card"><div class="ug-hostel-card__head"><div><h2>Review your reservation</h2><p>This is the bed space linked to your payment reference.</p></div><span class="ug-hostel-status ug-hostel-status--pending">Invoice ready</span></div><div class="ug-hostel-card__body"><div class="ug-hostel-location"><div class="ug-hostel-location__item"><small>Hall</small><strong>{{ $reservation->hall }}</strong></div><div class="ug-hostel-location__item"><small>Block</small><strong>{{ $reservation->block }}</strong></div><div class="ug-hostel-location__item"><small>Room / bed</small><strong>{{ $reservation->room }} / {{ $reservation->bed }}</strong></div></div><div class="ug-hostel-meta"><div class="ug-hostel-meta__row"><span>Reference</span><strong>00{{ $reservation->id }}</strong></div><div class="ug-hostel-meta__row"><span>Invoice description</span><strong>{{ $row->description }}</strong></div></div></div></section>
                        <section class="ug-hostel-card"><div class="ug-hostel-card__head"><div><h3>Complete payment</h3><p>Confirm the details, then continue to Remita.</p></div><span class="ug-hostel-status ug-hostel-status--pending">Awaiting payment</span></div><div class="ug-hostel-card__body"><div class="ug-hostel-rrr"><small>Remita Retrieval Reference (RRR)</small><strong>{{ $rrr }}</strong></div><div class="ug-hostel-meta"><div class="ug-hostel-meta__row"><span>Name</span><strong>{{ $row->name }}</strong></div><div class="ug-hostel-meta__row"><span>Phone</span><strong>{{ $row->phone }}</strong></div><div class="ug-hostel-meta__row"><span>Email</span><strong>{{ $row->email }}</strong></div>@if (isset($row->amount))<div class="ug-hostel-meta__row"><span>Amount</span><strong>₦{{ number_format((float) $row->amount, 2) }}</strong></div>@endif</div><form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg" method="POST" class="ug-hostel-action-form" style="margin-top:1rem"><input name="merchantId" value="{{ $merchantId }}" type="hidden"><input name="hash" value="{{ $paymentHash }}" type="hidden"><input name="rrr" value="{{ $rrr }}" type="hidden"><input name="responseurl" value="{{ env('REMITA_RESPONSE') }}response" type="hidden"><button type="submit" class="ug-hostel-button ug-hostel-button--danger ug-hostel-button--block" data-loading-label="Opening Remita…"><span class="ug-hostel-spinner" aria-hidden="true"></span><i class="fas fa-credit-card" aria-hidden="true"></i><span>Pay securely with Remita</span></button></form></div></section>
                    </div>
                @else
                    <div class="ug-hostel-alert"><h3>Further assistance is required</h3>Report to the Office of the Dean of Students (Student Affairs) with your reservation details.</div>
                @endif
            @endforeach
        @endforeach
    @elseif ($flag == 3)
        @foreach ($data as $row)
            <section class="ug-hostel-card"><div class="ug-hostel-success"><div class="ug-hostel-success__icon"><i class="fas fa-check" aria-hidden="true"></i></div><h2>Your hostel permit is ready</h2><p>Your payment has been recorded. Download your permit and keep it available when you move into the hostel.</p></div><div class="ug-hostel-card__body"><div class="ug-hostel-location"><div class="ug-hostel-location__item"><small>Hall</small><strong>{{ $row->hall }}</strong></div><div class="ug-hostel-location__item"><small>Block</small><strong>{{ $row->block }}</strong></div><div class="ug-hostel-location__item"><small>Room / bed</small><strong>{{ $row->room }} / {{ $row->bed }}</strong></div></div><div class="ug-hostel-meta"><div class="ug-hostel-meta__row"><span>Reference</span><strong>00{{ $row->id }}</strong></div></div><a class="ug-hostel-button ug-hostel-button--primary ug-hostel-button--block" href="{{ url('print-permit/'.$row->id) }}"><i class="fas fa-download" aria-hidden="true"></i> Download hostel permit</a></div></section>
        @endforeach
    @elseif ($flag == 4)
        <section class="ug-hostel-card"><div class="ug-hostel-card__head"><div><h2>Hostel applications are closed</h2></div><span class="ug-hostel-status ug-hostel-status--neutral">Closed</span></div><div class="ug-hostel-card__body"><div class="ug-hostel-empty"><i class="fas fa-lock" aria-hidden="true"></i><div>{{ $hostelClosedMessage }}</div></div><div class="ug-hostel-note"><strong>PIN status</strong>@if ($hostelPin) Your hostel PIN has been validated, but reservations are currently closed.@else You need a validated hostel PIN before applying when the window reopens.@endif</div></div></section>
    @elseif ($flag == 5)
        <section class="ug-hostel-alert"><h3>{{ $check['message'] }}</h3><ul>@foreach ($check['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></section>
    @endif

    <section class="ug-hostel-footer-notices" aria-label="Hostel guidance">
        <div class="ug-hostel-note">
            <strong>Use your allocation responsibly</strong>
            Hostel bed spaces and permits are personal and cannot be sold or transferred. Report suspicious activity to Hostel Management or Student Affairs.
        </div>
    </section>

</main>

<script>
    (function () {
        const page = document.querySelector('.ug-hostel-page');
        if (!page) return;

        const hall = document.getElementById('ug-hostel-hall');
        const block = document.getElementById('ug-hostel-block');
        const room = document.getElementById('ug-hostel-room');
        const bed = document.getElementById('ug-hostel-bed');
        const status = document.getElementById('ug-hostel-status');
        const csrf = page.querySelector('input[name="_token"]')?.value || '';

        const resetSelect = (select, label) => {
            if (!select) return;
            select.innerHTML = '<option value="">' + label + '</option>';
            select.disabled = true;
        };

        const loadOptions = async (url, payload, select, label) => {
            if (!select) return;
            select.disabled = true;
            select.innerHTML = '<option value="">Loading available options…</option>';
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ ...payload, _token: csrf })
                });
                if (!response.ok) throw new Error('Request failed');
                const html = await response.text();
                select.innerHTML = html || '<option value="">No options available</option>';
                select.disabled = !html || !select.options.length;
                if (status) status.textContent = select.disabled ? 'No available options were found. Try another selection.' : 'Select the next option to continue.';
            } catch (error) {
                select.innerHTML = '<option value="">Could not load options</option>';
                select.disabled = true;
                if (status) status.textContent = 'We could not load the available options. Please try again.';
            }
        };

        hall?.addEventListener('change', function () {
            resetSelect(room, 'Select a block first');
            resetSelect(bed, 'Select a room first');
            if (!this.value) { resetSelect(block, 'Select a hall first'); if (status) status.textContent = 'Start by selecting a hall.'; return; }
            loadOptions('{{ url('block') }}', { hall: this.value }, block, 'Select a block');
        });
        block?.addEventListener('change', function () {
            resetSelect(bed, 'Select a room first');
            if (!this.value) { resetSelect(room, 'Select a block first'); return; }
            loadOptions('{{ url('room') }}', { hall: hall.value, block: this.value }, room, 'Select a room');
        });
        room?.addEventListener('change', function () {
            if (!this.value) { resetSelect(bed, 'Select a room first'); return; }
            loadOptions('{{ url('bed') }}', { hall: hall.value, block: block.value, room: this.value }, bed, 'Select a bed');
        });

        page.querySelectorAll('form[data-loading-label], form.ug-hostel-action-form, #ug-hostel-reservation-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const button = form.querySelector('button[type="submit"], input[type="submit"]');
                if (!button || button.disabled) return;
                button.disabled = true;
                button.classList.add('is-loading');
                const label = button.querySelector('span:last-child');
                if (label && button.dataset.loadingLabel) label.textContent = button.dataset.loadingLabel;
                if (button.tagName === 'INPUT') button.value = button.dataset.loadingLabel || 'Please wait…';
            });
        });
    })();
</script>
