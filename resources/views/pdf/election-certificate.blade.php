@php
    $logo = public_path('uploads/logo-transparent.png');
    $signature = public_path('uploads/election-chairman-signature.png');
    try {
        $heldOn = \Carbon\Carbon::parse($electionDate)->format('l, jS F, Y');
    } catch (\Throwable $exception) {
        $heldOn = $electionDate;
    }
    // Explicit glyph positions render consistently in DomPDF; SVG textPath does not.
    $seal = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600"><circle cx="300" cy="300" r="217" fill="#fcfae7"/>';
    foreach ([['STUDENT UNION GOVERNMENT', -170, -10, '#f5e4e6', false], ['UNIVERSITY OF MAIDUGURI', 160, 20, '#e7e9e8', true]] as $arc) {
        $chars = str_split($arc[0]);
        foreach ($chars as $index => $char) {
            $angle = $arc[1] + ($arc[2] - $arc[1]) * $index / (count($chars) - 1);
            $x = 300 + 258 * cos(deg2rad($angle));
            $y = 300 + 258 * sin(deg2rad($angle));
            $rotation = $angle + ($arc[4] ? -90 : 90);
            $seal .= '<text x="0" y="0" text-anchor="middle" font-family="sans-serif" font-size="29" fill="'.$arc[3].'" transform="translate('.$x.' '.$y.') rotate('.$rotation.')">'.htmlspecialchars($char).'</text>';
        }
    }
    for ($star = 0; $star < 36; $star++) {
        $cx = 300 + 218 * cos(deg2rad($star * 10));
        $cy = 300 + 218 * sin(deg2rad($star * 10));
        $points = [];
        for ($vertex = 0; $vertex < 10; $vertex++) {
            $radius = $vertex % 2 === 0 ? 7 : 3;
            $a = deg2rad(-90 + $vertex * 36);
            $points[] = ($cx + $radius * cos($a)).','.($cy + $radius * sin($a));
        }
        $seal .= '<polygon points="'.implode(' ', $points).'" fill="#f8e7dd"/>';
    }
    $seal .= '</svg>';
    $sealSource = 'data:image/svg+xml;base64,'.base64_encode($seal);
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate of Return</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "Times-Roman", serif; color: #161616; }
        /* Content-box dimensions: padding and borders must not enlarge the canvas. */
        .certificate { position: relative; width: 297mm; height: 209mm; margin: 0; padding: 0; page-break-inside: avoid; }
        .next-page { page-break-before: always; }
        .frame { position: absolute; left: 5mm; top: 5mm; width: 285.5mm; height: 198.5mm; border: 0.65mm solid #315e4e; }
        .frame-inner { position: absolute; left: 6.5mm; top: 6.5mm; width: 282.5mm; height: 195.5mm; border: 0.3mm solid #315e4e; }
        .seal { position: absolute; left: 61mm; top: 17mm; width: 175mm; height: 175mm; }
        .watermark-crest { position: absolute; left: 117.5mm; top: 58mm; width: 62mm; height: auto; opacity: 0.10; }
        .center { position: absolute; left: 15mm; width: 267mm; text-align: center; }
        .logo { position: absolute; top: 15mm; left: 139mm; width: 19mm; height: auto; }
        .heading { top: 43mm; color: #303d49; font-size: 16pt; font-weight: bold; line-height: 1.05; }
        .title { top: 63mm; font-size: 29pt; font-weight: bold; font-style: italic; color: #142e42; line-height: 1; }
        .certify { top: 83mm; font-size: 12pt; font-style: italic; }
        .name { top: 92mm; height: 10mm; font-weight: bold; color: #1d3142; line-height: 1.05; }
        .name-rule { position: absolute; left: 22mm; top: 103mm; width: 253mm; border-top: 0.35mm solid #b6aa79; }
        .elected { top: 109mm; font-size: 12pt; }
        .office { top: 119mm; font-weight: bold; color: #c70000; line-height: 1.08; }
        .institution { top: 131mm; font-size: 18pt; }
        .dates { top: 141mm; font-size: 12pt; line-height: 1.35; }
        .signature-image { position: absolute; top: 160mm; left: 129.5mm; width: 38mm; height: auto; }
        .signature-rule { position: absolute; left: 114mm; top: 179mm; width: 69mm; border-top: 0.2mm solid #727572; }
        .signer { top: 180mm; font-size: 12pt; line-height: 1.3; }
    </style>
</head>
<body>
@foreach($rows as $row)
    @php
        $candidateName = mb_strtoupper(trim((string) ($row->name ?: 'The named candidate')));
        $position = mb_strtoupper(trim((string) ($row->position ?: 'ELECTED OFFICE')));
        $office = $row->category === 'General' ? $position.', STUDENT UNION GOVERNMENT (SUG)' : $position;
        $entity = trim((string) ($row->entity_label ?? ''));
        if ($row->category !== 'General' && $entity !== '') {
            $office .= ' - '.mb_strtoupper($entity);
        }
        $fontMetrics = $pdfFontMetrics ?? null;
        $nameSize = 21;
        $officeSize = 18;
        if ($fontMetrics) {
            $font = $fontMetrics->getFont('Times-Roman', 'bold');
            while ($nameSize > 12 && $fontMetrics->getTextWidth($candidateName, $font, $nameSize) > 705) { $nameSize -= 0.5; }
            while ($officeSize > 11 && $fontMetrics->getTextWidth($office, $font, $officeSize) > 745) { $officeSize -= 0.5; }
        } else {
            $nameSize = mb_strlen($candidateName) > 48 ? 14 : 21;
            $officeSize = mb_strlen($office) > 75 ? 12 : 18;
        }
    @endphp
    <div class="certificate{{ $loop->first ? '' : ' next-page' }}">
        <img class="seal" src="{{ $sealSource }}" alt="">
        <img class="watermark-crest" src="{{ $logo }}" alt="">
        <div class="frame"></div>
        <div class="frame-inner"></div>
        <img class="logo" src="{{ $logo }}" alt="University of Maiduguri">
        <div class="center heading">STUDENT UNION GOVERNMENT (SUG)<br>UNIVERSITY OF MAIDUGURI</div>
        <div class="center title">CERTIFICATE OF RETURN</div>
        <div class="center certify">This is to certify that</div>
        <div class="center name" style="font-size: {{ $nameSize }}pt">{{ $candidateName }}</div>
        <div class="name-rule"></div>
        <div class="center elected">Has been duly elected as</div>
        <div class="center office" style="font-size: {{ $officeSize }}pt">{{ $office }}</div>
        <div class="center institution">UNIVERSITY OF MAIDUGURI</div>
        <div class="center dates">Held on {{ $heldOn }}<br>Dated this {{ now()->format('jS') }} Day of {{ now()->format('F, Y') }}</div>
        <img class="signature-image" src="{{ $signature }}" alt="Chairman's signature">
        <div class="signature-rule"></div>
        <div class="center signer">Sulaiman Sulaiman Dauda<br>Chairman,<br>Electoral Committee, SUG Elections {{ $session ?: '' }}</div>
    </div>
@endforeach
</body>
</html>
