@php
    $logo = public_path('uploads/logo.png');
    $watermark = public_path('uploads/logo-transparent.png');
    $categoryTitle = ['General' => 'STUDENT UNION GOVERNMENT (SUG)', 'Faculty Rep' => 'FACULTY REPRESENTATIVE', 'Hostel Rep' => 'HOSTEL REPRESENTATIVE', 'Lga Rep' => 'LGA REPRESENTATIVE'];
@endphp
<!doctype html>
<html><head><meta charset="utf-8"><style>
    @page{size:A4 landscape;margin:0}*{box-sizing:border-box}body{margin:0;background:#fff;font-family:DejaVu Serif,Georgia,serif;color:#1d3151}.certificate{position:relative;width:100%;height:700px;min-height:700px;max-height:700px;padding:18px 62px 16px;border:5px double #2d6b50;page-break-inside:avoid;overflow:hidden;background:#fff}.certificate-break{page-break-after:always}.certificate:before{content:"";position:absolute;inset:10px;border:1px solid #b49758;pointer-events:none}.certificate:after{content:"";position:absolute;inset:15px;border:1px solid rgba(45,107,80,.3);pointer-events:none}.watermark{position:absolute;z-index:0;left:50%;top:53%;width:385px;height:385px;object-fit:contain;transform:translate(-50%,-50%);opacity:.075}.content{position:relative;z-index:1;text-align:center}.header{display:block;text-align:center}.logo{display:block;width:72px;height:75px;margin:0 auto 3px;object-fit:contain}.sug{margin:0;font-size:16px;line-height:1.1;font-weight:bold;letter-spacing:.2px;color:#263d5d}.university{margin:1px 0 0;font-size:15px;line-height:1.1;letter-spacing:.35px;font-weight:bold;color:#263d5d}.certificate-title{margin:15px 0 10px;font-size:29px;line-height:1.05;color:#1f395f;font-style:italic;font-weight:bold;letter-spacing:.35px}.certify{display:inline-block;padding:4px 16px;border-radius:2px;background:rgba(225,231,232,.5);font-size:12px;font-style:italic;color:#536574}.rule{height:1px;margin:10px auto 12px;background:#b49758}.statement{font-size:15px;line-height:1.2}.candidate-name{margin:8px 0 0;font-size:20px;line-height:1.1;font-weight:bold;color:#1e4369;text-transform:uppercase}.elected-line{margin-top:5px}.position{margin:8px auto 3px;max-width:86%;color:#c51622;font-size:18px;line-height:1.1;font-weight:bold;text-transform:uppercase;overflow-wrap:break-word}.category{font-size:14px;line-height:1.15;font-weight:bold;color:#1d3151}.details{margin-top:6px;font-size:11px;line-height:1.4;color:#2f3b48}.signature-wrap{display:flex;justify-content:center;width:100%;margin-top:18px;text-align:center}.signature{width:220px;margin:0 auto;border-top:1px solid #273949;padding-top:4px;text-align:center;font-size:11px;line-height:1.25;color:#273949}.serial{position:absolute;right:28px;bottom:17px;color:#657784;font-family:DejaVu Sans,Arial,sans-serif;font-size:8px}
</style></head><body>
@foreach($rows as $row)
    @php
        $candidateName = trim((string) ($row->name ?: 'The named candidate'));
        $position = trim((string) ($row->position ?: 'ELECTED OFFICE'));
        $nameSize = strlen($candidateName) > 38 ? 17 : (strlen($candidateName) > 28 ? 19 : 21);
        $positionSize = strlen($position) > 48 ? 13 : (strlen($position) > 34 ? 15 : 18);
    @endphp
    <div class="certificate @if (!$loop->last) certificate-break @endif">
        <img class="watermark" src="{{ $watermark }}" alt="">
        <div class="content">
            <div class="header">
                <img class="logo" src="{{ $logo }}" alt="University of Maiduguri logo">
                <div class="sug">STUDENT UNION GOVERNMENT (SUG)</div>
                <div class="university">UNIVERSITY OF MAIDUGURI</div>
            </div>
            <div class="certificate-title">CERTIFICATE OF RETURN</div>
            <div class="certify">This is to certify that</div>
            <div class="statement candidate-name" style="font-size:{{ $nameSize }}px">{{ $candidateName }}</div>
            <div class="rule"></div>
            <div class="statement elected-line">Has been duly elected as</div>
            <div class="position" style="font-size:{{ $positionSize }}px">{{ $position }}</div>
            <div class="category">{{ $categoryTitle[$row->category] ?? strtoupper($row->category) }}</div>
            <div class="details">UNIVERSITY OF MAIDUGURI<br>Held on {{ $electionDate }}<br>Dated this {{ now()->format('jS') }} day of {{ now()->format('F, Y') }}</div>
            <div class="signature-wrap"><div class="signature">Chairman,<br>Electoral Committee, SUG Elections {{ $session ?: '' }}</div></div>
        </div>
        <div class="serial">Candidate ID: {{ $row->candidate }}</div>
    </div>
@endforeach
</body></html>
