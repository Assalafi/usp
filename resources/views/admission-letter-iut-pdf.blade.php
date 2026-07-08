@php
    use Illuminate\Support\Facades\DB;

    $romanNumerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
    $numberWords = [1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six'];

    $fullname = strtoupper(trim($application->surname . ' ' . $application->first_name . ' ' . ($application->middle_name ?? '')));

    $newDept = DB::table('department')->where('code', $application->new_department)->first();
    $newFac = DB::table('faculty')->where('code', $application->new_faculty)->first();
    $newProgram = DB::table('program')->where('code', $application->new_program)->first();

    // Determine level/part from year_of_study
    $yearNum = intval($application->year_of_study ?? 1);
    if ($yearNum >= 100) $yearNum = intval($yearNum / 100);
    $yearNum = max(1, min(6, $yearNum));
    $partRoman = $romanNumerals[$yearNum] ?? 'I';
    $partWord = $numberWords[$yearNum] ?? 'One';

    // Format date with ordinal suffix
    $vcDate = $application->vc_date ? strtotime($application->vc_date) : time();
    $day = intval(date('j', $vcDate));
    if (in_array($day, [11, 12, 13])) {
        $suffix = 'th';
    } elseif ($day % 10 === 1) {
        $suffix = 'st';
    } elseif ($day % 10 === 2) {
        $suffix = 'nd';
    } elseif ($day % 10 === 3) {
        $suffix = 'rd';
    } else {
        $suffix = 'th';
    }
    $formattedDate = $day . '<sup>' . $suffix . '</sup> ' . date('F, Y', $vcDate);

    // Session
    $sessionText = $application->session ?? (date('Y') . '/' . (date('Y') + 1));
@endphp
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $application->application_no }} - UNIMAID - INTER-UNIVERSITY TRANSFER</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        body {
            font-family: "Times New Roman", serif;
            color: black;
            font-size: 12pt;
        }

        h1 {
            color: #000080;
            font-family: "Times New Roman", serif;
            font-size: 22pt;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            color: #C20707;
            font-family: "Times New Roman", serif;
            font-size: 14pt;
            font-style: italic;
            text-align: center;
        }

        .office-title {
            font-family: "Times New Roman", serif;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
        }

        .letter-title {
            font-family: "Times New Roman", serif;
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
        }

        .body-text {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            text-align: justify;
            line-height: 1.6;
        }

        .registrar-name {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            font-weight: bold;
        }

        .contact-info {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
        }

        .header-wrap {
            padding: 20pt 30pt 0 30pt;
            text-align: center;
        }

        .content-wrap {
            padding: 0 50pt;
        }

        .line {
            border-top: 2px solid black;
            margin: 5pt 0;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -1;
            width: 400px;
            height: 400px;
            background-image: url('{{ public_path('uploads/logo.png') }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .sig-name {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            font-weight: bold;
        }

        .sig-title {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
        }
    </style>
</head>

<body>
    <div class="watermark"></div>

    <div class="header-wrap">
        <div style="text-align: center; margin-bottom: 5pt;">
            <img src="{{ public_path('uploads/logo.png') }}" alt="University Logo"
                style="width: 80px; height: 80px; object-fit: contain;" />
        </div>
        <h1>UNIVERSITY OF MAIDUGURI</h1>
        <p class="subtitle">...Centre of Excellence</p>
        <p style="font-size: 10pt; text-align: center; margin-top: 2pt;">P.M.B. 1069, MAIDUGURI, NIGERIA</p>
        <p style="font-size: 10pt; text-align: center;">Tel: +234 8086980044</p>

        <table style="width: 100%; margin-top: 5pt; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; vertical-align: top; width: 60%;">
                    <p class="registrar-name">AHMAD A. LAWAN, <span style="font-size: 9pt;">BA (Ed), MPA, MSc, MNM, FICA</span></p>
                </td>
                <td style="text-align: right; vertical-align: top; width: 40%;">
                    <p class="contact-info"><strong>EMAIL:</strong><br />registrar@unimaid.edu.ng</p>
                </td>
            </tr>
        </table>

        <div class="line"></div>
        <p class="office-title" style="margin: 8pt 0;">OFFICE OF THE REGISTRAR</p>
    </div>

    <div class="content-wrap">
        {{-- Reference and Date --}}
        <table style="width: 100%; margin-top: 10pt; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-size: 11pt;">R/ACA.216/VOL.IX</td>
                <td style="text-align: right; font-size: 11pt;">{!! $formattedDate !!}</td>
            </tr>
        </table>

        {{-- Addressee --}}
        <div style="margin-top: 15pt;">
            <p style="font-size: 12pt; font-weight: bold;">{{ $fullname }}</p>
            <p style="font-size: 12pt;">{{ $application->present_institution }}</p>
        </div>

        {{-- Title --}}
        <p class="letter-title" style="margin-top: 20pt;">INTER-UNIVERSITY TRANSFER</p>

        {{-- Body --}}
        <div style="margin-top: 15pt;">
            <p class="body-text">Following your application for transfer from {{ $application->present_institution }} to University of Maiduguri, I write to inform you that the Vice-Chancellor has given the approval for your transfer into the University of Maiduguri. You have been placed into the Department of <strong>{{ $newDept->title ?? '' }}</strong> Part {{ $partRoman }} ({{ $partWord }}) with effect from the {{ $sessionText }} Academic Session.</p>
        </div>

        <div style="margin-top: 12pt;">
            <p class="body-text">You are requested to report to the Faculty Officer, {{ $newFac->title ?? '' }} for your registration formalities.</p>
        </div>

        <div style="margin-top: 12pt;">
            <p class="body-text">Please accept my congratulations.</p>
        </div>

        {{-- Closing --}}
        <div style="margin-top: 20pt;">
            <p class="body-text">Yours Sincerely,</p>
        </div>

        {{-- Signature --}}
        <table style="width: 100%; margin-top: 15pt; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; vertical-align: bottom; width: 65%;">
                    <p class="sig-name" style="margin-top: 40pt;">Zara Umar Ibrahim</p>
                    <p class="sig-title">Director, Senate and Academic Matters</p>
                    <p class="sig-title"><strong>For: REGISTRAR</strong></p>
                </td>
                <td style="text-align: right; vertical-align: bottom; width: 35%;">
                    @php
                        $qrData =
                            'UNIMAID Inter-University Transfer' .
                            PHP_EOL .
                            "Name: {$fullname}" .
                            PHP_EOL .
                            "From: {$application->present_institution}" .
                            PHP_EOL .
                            "Ref: {$application->application_no}" .
                            PHP_EOL .
                            "Dept: " . ($newDept->title ?? '') .
                            PHP_EOL .
                            'Session: ' . $sessionText .
                            PHP_EOL .
                            'Verified: UNIMAID';
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&ecc=M&data={{ urlencode($qrData) }}"
                        alt="QR Code"
                        style="width: 80px; height: 80px; border: 2px solid #000; padding: 4px;" />
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
