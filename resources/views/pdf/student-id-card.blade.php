<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card</title>
    <style>
        @page { margin: 0; size: 85.6mm 53.98mm; }
        * { box-sizing: border-box; }
        html, body {
            width: 85.6mm;
            height: 53.98mm;
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #102a43;
            background: #ffffff;
        }
        .card-page {
            position: relative;
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
            page-break-after: always;
            background: #f8fbfe;
        }
        .card-page:last-child { page-break-after: avoid; }
        .front { background: #f8fbfe; }
        .top-band {
            position: absolute;
            left: 0;
            top: 0;
            width: 85.6mm;
            height: 13.3mm;
            background: #083b66;
        }
        .top-accent {
            position: absolute;
            left: 0;
            top: 0;
            width: 3.3mm;
            height: 13.3mm;
            background: #19a7ce;
        }
        .brand-logo {
            position: absolute;
            left: 4.8mm;
            top: 2.15mm;
            width: 8.8mm;
            height: 8.8mm;
            object-fit: contain;
            background: #ffffff;
            border-radius: 1.5mm;
            padding: .65mm;
        }
        .brand-title {
            position: absolute;
            left: 15.2mm;
            top: 3mm;
            color: #ffffff;
            font-size: 8.2pt;
            font-weight: bold;
            letter-spacing: .2pt;
            line-height: 1.1;
        }
        .brand-subtitle {
            position: absolute;
            left: 15.2mm;
            top: 8.25mm;
            color: #bfe9f6;
            font-size: 4.7pt;
            letter-spacing: .65pt;
            text-transform: uppercase;
        }
        .card-type {
            position: absolute;
            right: 3.4mm;
            top: 4.1mm;
            color: #ffffff;
            font-size: 4.8pt;
            font-weight: bold;
            letter-spacing: .5pt;
            text-align: right;
            text-transform: uppercase;
        }
        .photo-frame {
            position: absolute;
            left: 3.4mm;
            top: 16.25mm;
            width: 22.8mm;
            height: 28.3mm;
            padding: .8mm;
            border: .45mm solid #d6e3ee;
            border-radius: 1.8mm;
            background: #ffffff;
        }
        .photo-frame img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1.1mm;
        }
        .identity {
            position: absolute;
            left: 29mm;
            top: 16.2mm;
            width: 53mm;
            height: 28.7mm;
        }
        .student-name {
            color: #083b66;
            font-size: 9pt;
            line-height: 1.12;
            font-weight: bold;
            text-transform: uppercase;
            max-width: 51mm;
            height: 7mm;
            overflow: hidden;
        }
        .matric {
            display: inline-block;
            margin-top: .8mm;
            padding: .8mm 1.7mm;
            border-radius: 1.1mm;
            background: #dff3f9;
            color: #05688b;
            font-size: 6.4pt;
            font-weight: bold;
            letter-spacing: .35pt;
        }
        .data-row {
            width: 51.5mm;
            height: 5.3mm;
            border-bottom: .18mm solid #dfeaf1;
            padding-top: 1.15mm;
            white-space: nowrap;
            overflow: hidden;
        }
        .data-label {
            display: inline-block;
            width: 14.5mm;
            color: #718096;
            font-size: 4.65pt;
            font-weight: bold;
            letter-spacing: .25pt;
            text-transform: uppercase;
            vertical-align: top;
        }
        .data-value {
            display: inline-block;
            width: 36mm;
            color: #243b53;
            font-size: 5.5pt;
            font-weight: bold;
            text-transform: uppercase;
            vertical-align: top;
            overflow: hidden;
        }
        .bottom-band {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 85.6mm;
            height: 8.1mm;
            background: #e7f1f8;
            border-top: .35mm solid #b8d3e3;
        }
        .bottom-meta {
            position: absolute;
            left: 3.5mm;
            top: 1.05mm;
            color: #315c79;
            font-size: 4.3pt;
            line-height: 1.55;
            text-transform: uppercase;
        }
        .bottom-meta strong { color: #083b66; }
        .signature {
            position: absolute;
            right: 3.4mm;
            bottom: 1.25mm;
            width: 19mm;
            height: 5.3mm;
            border-bottom: .22mm solid #6b879b;
            text-align: center;
        }
        .signature img {
            max-width: 16mm;
            max-height: 3.7mm;
            object-fit: contain;
        }
        .signature-caption {
            position: absolute;
            right: 3.4mm;
            bottom: .2mm;
            width: 19mm;
            color: #718096;
            font-size: 3.5pt;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .2pt;
        }
        .back {
            background: #083b66;
        }
        .back-header {
            position: absolute;
            left: 0;
            top: 0;
            width: 85.6mm;
            height: 12.7mm;
            background: #062e50;
        }
        .back-header .brand-logo {
            left: 4.8mm;
            top: 1.8mm;
        }
        .back-heading {
            position: absolute;
            left: 15.2mm;
            top: 3.1mm;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: .35pt;
            text-transform: uppercase;
        }
        .back-kicker {
            position: absolute;
            left: 15.2mm;
            top: 8mm;
            color: #bfe9f6;
            font-size: 4.4pt;
            letter-spacing: .6pt;
            text-transform: uppercase;
        }
        .back-panel {
            position: absolute;
            left: 3.4mm;
            top: 15mm;
            width: 78.8mm;
            height: 35.5mm;
            padding: 3.2mm;
            border-radius: 2mm;
            background: #ffffff;
        }
        .qr-box {
            position: absolute;
            left: 5.5mm;
            top: 19.2mm;
            width: 24.2mm;
            height: 24.2mm;
            padding: 1.5mm;
            border: .4mm solid #c8dce9;
            border-radius: 1.7mm;
            background: #ffffff;
        }
        .qr-box img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .back-details {
            position: absolute;
            left: 35mm;
            top: 18.1mm;
            width: 43.5mm;
            color: #294e68;
        }
        .back-label {
            color: #718096;
            font-size: 4.2pt;
            letter-spacing: .35pt;
            text-transform: uppercase;
        }
        .back-value {
            margin-top: .45mm;
            color: #133b56;
            font-size: 5.65pt;
            font-weight: bold;
            line-height: 1.17;
            text-transform: uppercase;
            max-height: 5.7mm;
            overflow: hidden;
        }
        .back-rule {
            width: 43mm;
            margin: 1.35mm 0 1.2mm;
            border-top: .2mm solid #dfeaf1;
        }
        .back-note {
            position: absolute;
            left: 5.5mm;
            bottom: 3.1mm;
            width: 70mm;
            color: #d9edf6;
            font-size: 4.1pt;
            line-height: 1.35;
            text-align: center;
        }
        .security {
            position: absolute;
            right: 4.2mm;
            top: 14.1mm;
            color: #8ed8eb;
            font-size: 3.7pt;
            letter-spacing: .25pt;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <section class="card-page front">
        <div class="top-band"></div>
        <div class="top-accent"></div>
        @if($logoData)
            <img class="brand-logo" src="{{ $logoData }}" alt="">
        @endif
        <div class="brand-title">UNIVERSITY OF MAIDUGURI</div>
        <div class="brand-subtitle">Official student identity card</div>
        <div class="card-type">{{ $cardType }}</div>

        <div class="photo-frame">
            @if($photoData)
                <img src="{{ $photoData }}" alt="">
            @endif
        </div>

        <div class="identity">
            <div class="student-name">{{ $fullName }}</div>
            <div class="matric">{{ $matric }}</div>
            <div class="data-row">
                <span class="data-label">Programme</span>
                <span class="data-value">{{ $programTitle }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Faculty</span>
                <span class="data-value">{{ $facultyTitle }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Department</span>
                <span class="data-value">{{ $departmentTitle }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Level</span>
                <span class="data-value">{{ $student->level ?: 'Not set' }}</span>
            </div>
        </div>

        <div class="bottom-band">
            <div class="bottom-meta">
                <div><strong>Issued:</strong> {{ $issueDate }} &nbsp;&nbsp; <strong>Valid until:</strong> {{ $expiryDate }}</div>
                <div><strong>Property of the University of Maiduguri</strong></div>
            </div>
            @if($signatureData)
                <div class="signature"><img src="{{ $signatureData }}" alt=""></div>
            @endif
            <div class="signature-caption">Student signature</div>
        </div>
    </section>

    <section class="card-page back">
        <div class="back-header">
            @if($logoData)
                <img class="brand-logo" src="{{ $logoData }}" alt="">
            @endif
            <div class="back-heading">Student identity card</div>
            <div class="back-kicker">Scan to confirm card details</div>
        </div>
        <div class="security">Keep this card safe</div>
        <div class="back-panel"></div>

        @if($qrData)
            <div class="qr-box"><img src="{{ $qrData }}" alt="Verification QR code"></div>
        @endif

        <div class="back-details">
            <div class="back-label">Card holder</div>
            <div class="back-value">{{ $fullName }}</div>
            <div class="back-rule"></div>
            <div class="back-label">Emergency contact</div>
            <div class="back-value">{{ $student->kin_name ?: 'Not provided' }}</div>
            <div class="back-value">{{ $student->kin_phone ?: 'Not provided' }}</div>
            <div class="back-rule"></div>
            <div class="back-label">Instructions</div>
            <div class="back-value">If found, return this card to the University of Maiduguri.</div>
        </div>

        <div class="back-note">This card is issued for official university identification only. It is not transferable.</div>
    </section>
</body>
</html>