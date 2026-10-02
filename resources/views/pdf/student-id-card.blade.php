<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University of Maiduguri - Undergraduate ID Card</title>
    <style>
        @page { size: 53.98mm 85.6mm; margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 6pt; color: #172e45; }
        .card { position: relative; width: 53.98mm; height: 85.59mm; background: #fff; }
        .back { page-break-before: always; }
        .header { position: absolute; top: 0; left: 0; width: 53.98mm; height: 17.2mm; background: #3ea1e4; }
        .crest { position: absolute; left: 3.3mm; top: 2.9mm; width: 9mm; height: 11mm; background: #fff; border-radius: 1mm; }
        .crest img, .photo img, .signature-image img { position: absolute; display: block; }
        .university { position: absolute; left: 14.3mm; top: 3.4mm; color: #fff; font-weight: bold; line-height: 1.2; }
        .university .overline { font-size: 7.2pt; }
        .university .title { font-size: 10.2pt; }
        .address { position: absolute; left: 14.3mm; top: 12.4mm; color: #d5e7f5; font-size: 4.55pt; }
        .type-band { position: absolute; left: 0; top: 17.2mm; width: 53.98mm; height: 5.3mm; background: #123451; text-align: center; }
        .type-band span { display: block; margin-top: .8mm; color: #fff; font-size: 6pt; font-weight: bold; letter-spacing: .45pt; }
        .portrait-tint { position: absolute; left: 0; top: 22.5mm; width: 53.98mm; height: 15mm; background: #eaf4fb; }
        .photo-border { position: absolute; left: 15.79mm; top: 23.55mm; width: 22mm; height: 23.5mm; border: .2mm solid #bccfdf; background: #fff; }
        .photo { position: absolute; left: .5mm; top: .5mm; width: 21mm; height: 22.5mm; background: #fff; }
        .photo-empty { margin-top: 8mm; text-align: center; color: #72889b; font-size: 5pt; }
        .name { position: absolute; top: 48.4mm; left: 3.49mm; width: 47mm; text-align: center; font-weight: bold; line-height: 1.05; }
        .matric { position: absolute; top: 55.8mm; left: 3.49mm; width: 47mm; text-align: center; font-weight: bold; color: #155d8f; line-height: 1.1; }
        .name-rule { position: absolute; left: 20.99mm; top: 59.6mm; width: 12mm; border-top: .45mm solid #d6ad54; }
        .field { position: absolute; left: 3.49mm; width: 47mm; text-align: center; }
        .label { color: #536d83; font-size: 4.5pt; letter-spacing: .35pt; line-height: 1.15; }
        .value { font-weight: bold; line-height: 1.15; }
        .programme-label { top: 60.6mm; }
        .programme-value { top: 62.8mm; line-height: 1; }
        .faculty-label { top: 69mm; }
        .faculty-value { top: 71.3mm; line-height: 1; }
        .origin { position: absolute; left: 3.49mm; top: 77.4mm; width: 47mm; border-top: .18mm solid #dce6ed; }
        .origin-column { position: absolute; top: .5mm; width: 22mm; }
        .origin-column.right { left: 25mm; }
        .origin-column .label { font-size: 4.2pt; }
        .origin-column .value { margin-top: .1mm; line-height: 1; }
        .edge { position: absolute; left: 0; top: 84.5mm; width: 53.98mm; height: 1.1mm; background: #123451; }
        .edge-accent { position: absolute; left: 0; top: 84.5mm; width: 17mm; height: 1.1mm; background: #3ea1e4; }

        .back-heading { position: absolute; left: 0; top: 0; width: 53.98mm; height: 8.5mm; background: #3ea1e4; text-align: center; color: #fff; }
        .back-heading .heading { margin-top: 1.7mm; font-size: 6.5pt; font-weight: bold; letter-spacing: .35pt; }
        .back-heading .subheading { margin-top: .35mm; font-size: 4.3pt; color: #c9e2f5; letter-spacing: .6pt; }
        .ownership { position: absolute; left: 3.49mm; top: 11.1mm; width: 47mm; font-size: 5.65pt; line-height: 1.25; color: #334e65; }
        .return { margin-top: 1mm; font-weight: bold; color: #172e45; }
        .qr-panel { position: absolute; left: 3.49mm; top: 31mm; width: 19mm; height: 19mm; border: .18mm solid #d9e4ed; background: #fff; }
        .qr-panel img { display: block; width: 19mm; height: 19mm; }
        .qr-label { position: absolute; left: 3.49mm; top: 50.7mm; width: 19mm; text-align: center; font-size: 4pt; color: #536d83; }
        .date { position: absolute; left: 26mm; width: 24.5mm; }
        .date.issued { top: 32mm; }
        .date.expires { top: 41mm; }
        .date .value { margin-top: 1mm; font-size: 6.5pt; }
        .emergency { position: absolute; left: 3.49mm; top: 54.3mm; width: 47mm; }
        .emergency .label { color: #155d8f; font-size: 4.7pt; }
        .emergency-name { position: absolute; left: 3.49mm; top: 57.1mm; width: 47mm; line-height: 1.05; }
        .emergency-phone { position: absolute; left: 3.49mm; top: 63.4mm; width: 47mm; }
        .signatures { position: absolute; left: 3.49mm; top: 67.1mm; width: 47mm; }
        .signature-column { position: absolute; left: 0; width: 21mm; }
        .signature-column.registrar { left: 26mm; }
        .signature-image { position: relative; margin-left: 1mm; width: 19mm; height: 6.5mm; }
        .signature-label { margin-top: .3mm; padding-top: .7mm; border-top: .18mm solid #91a6b8; color: #334e65; font-size: 4.6pt; text-align: center; }
        .disclaimer { position: absolute; left: 3.49mm; top: 77.7mm; width: 47mm; color: #536d83; font-size: 4.8pt; line-height: 1.2; text-align: center; }
    </style>
</head>
<body>
    <div class="card front">
        <div class="header"></div>
        <div class="crest">
            @if($logo['src'])
                <img src="{{ $logo['src'] }}" style="left: {{ $logo['left'] }}mm; top: {{ $logo['top'] }}mm; width: {{ $logo['width'] }}mm; height: {{ $logo['height'] }}mm;" alt="">
            @endif
        </div>
        <div class="university">
            <div class="overline">UNIVERSITY OF</div>
            <div class="title">MAIDUGURI</div>
        </div>
        <div class="address">P.M.B. 1069, Maiduguri, Nigeria</div>
        <div class="type-band"><span>UNDERGRADUATE IDENTITY CARD</span></div>
        <div class="portrait-tint"></div>
        <div class="photo-border">
            <div class="photo">
                @if($photo['src'])
                    <img src="{{ $photo['src'] }}" style="left: {{ $photo['left'] }}mm; top: {{ $photo['top'] }}mm; width: {{ $photo['width'] }}mm; height: {{ $photo['height'] }}mm;" alt="">
                @else
                    <div class="photo-empty">PHOTO NOT AVAILABLE</div>
                @endif
            </div>
        </div>
        <div class="name" style="font-size: {{ $nameText['size'] }}pt;">{!! nl2br(e($nameText['text'])) !!}</div>
        <div class="matric" style="font-size: {{ $matricText['size'] }}pt;">{{ $matricText['text'] }}</div>
        <div class="name-rule"></div>

        <div class="field programme-label label">COURSE OF STUDY</div>
        <div class="field programme-value value" style="font-size: {{ $programText['size'] }}pt;">{!! nl2br(e($programText['text'])) !!}</div>
        <div class="field faculty-label label">FACULTY / COLLEGE</div>
        <div class="field faculty-value value" style="font-size: {{ $facultyText['size'] }}pt;">{!! nl2br(e($facultyText['text'])) !!}</div>
        <div class="origin">
            <div class="origin-column">
                <div class="label">STATE OF ORIGIN</div>
                <div class="value" style="font-size: {{ $stateText['size'] }}pt;">{{ $stateText['text'] }}</div>
            </div>
            <div class="origin-column right">
                <div class="label">NATIONALITY</div>
                <div class="value" style="font-size: {{ $nationalityText['size'] }}pt;">{{ $nationalityText['text'] }}</div>
            </div>
        </div>
        <div class="edge"></div><div class="edge-accent"></div>
    </div>

    <div class="card back">
        <div class="back-heading">
            <div class="heading">UNIVERSITY OF MAIDUGURI</div>
            <div class="subheading">STUDENT IDENTITY CARD</div>
        </div>
        <div class="ownership">
            This card is the property of the University of Maiduguri and identifies only the holder whose photograph is on the front.
            <div class="return">If found, please return it to the Registrar.</div>
        </div>
        @if($qrData)
            <div class="qr-panel"><img src="{{ $qrData }}" alt="Card details QR code"></div>
            <div class="qr-label">SCAN FOR CARD DETAILS</div>
        @endif
        <div class="date issued">
            <div class="label">DATE ISSUED</div>
            <div class="value">{{ $issueDate }}</div>
        </div>
        <div class="date expires">
            <div class="label">EXPIRY DATE</div>
            <div class="value">{{ $expiryDate }}</div>
        </div>
        <div class="emergency"><div class="label">IN CASE OF EMERGENCY, CONTACT</div></div>
        <div class="emergency-name value" style="font-size: {{ $kinText['size'] }}pt;">{!! nl2br(e($kinText['text'])) !!}</div>
        <div class="emergency-phone value" style="font-size: {{ $kinPhoneText['size'] }}pt;">{{ $kinPhoneText['text'] }}</div>

        <div class="signatures">
            <div class="signature-column">
                <div class="signature-image">
                    @if($signature['src'])
                        <img src="{{ $signature['src'] }}" style="left: {{ $signature['left'] }}mm; top: {{ $signature['top'] }}mm; width: {{ $signature['width'] }}mm; height: {{ $signature['height'] }}mm;" alt="">
                    @endif
                </div>
                <div class="signature-label">Holder's signature</div>
            </div>
            <div class="signature-column registrar">
                <div class="signature-image">
                    @if($registrarSignature['src'])
                        <img src="{{ $registrarSignature['src'] }}" style="left: {{ $registrarSignature['left'] }}mm; top: {{ $registrarSignature['top'] }}mm; width: {{ $registrarSignature['width'] }}mm; height: {{ $registrarSignature['height'] }}mm;" alt="">
                    @endif
                </div>
                <div class="signature-label">Registrar</div>
            </div>
        </div>
        <div class="disclaimer">The University disclaims responsibility for any improper use of this card.</div>
        <div class="edge"></div><div class="edge-accent"></div>
    </div>
</body>
</html>
