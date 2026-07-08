@php
    use Illuminate\Support\Facades\DB;
@endphp
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $applicant->username }} - UNIMAID - ADMISSION LETTER</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        body {
            font-family: Arial, sans-serif;
            color: black;
        }

        .s1 {
            font-family: "Courier New", monospace;
            font-size: 12pt;
        }

        h1 {
            color: #000080;
            font-family: Arial, sans-serif;
            font-size: 30pt;
            font-weight: bold;
            line-height: 31pt;
        }

        h2 {
            color: #C20707;
            font-family: "Times New Roman", serif;
            font-size: 22pt;
            font-weight: bold;
            line-height: 20pt;
        }

        .s2 {
            color: #C20707;
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            font-weight: bold;
        }

        .s3 {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            font-weight: bold;
        }

        .a {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            font-weight: bold;
        }

        .s5 {
            color: #C20707;
            font-family: "Times New Roman", serif;
            font-size: 16pt;
            font-weight: bold;
        }

        p {
            font-family: Arial, sans-serif;
            font-size: 13.5pt;
            margin: 0;
        }

        .s6 {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            font-weight: bold;
        }

        .header {
            padding-left: 25pt;
            padding-right: 25pt;
            text-align: center;
        }

        .right-align {
            text-align: right;
            padding-right: 5pt;
        }

        .left-align {
            text-align: left;
            padding-left: 5pt;
            padding-right: 5pt;
        }

        .center-align {
            text-align: center;
            padding-left: 53pt;
            padding-right: 53pt;
        }

        .content {
            padding-left: 5pt;
            padding-right: 5pt;
            line-height: 91%;
            text-align: justify;
            /* line spacing */
            line-height: 1.3;
            /* margin-left: 30px; */
        }

        .content-left-margin {
            margin-left: 30px;
            margin-right: 30px;
        }

        .signature {
            padding-left: 5pt;
            padding-right: 5pt;
        }

        .line {
            border-top: 2px solid black;
            width: calc(100% + 62pt);
            margin: 0;
            margin-left: -31pt;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            width: 500px;
            height: 500px;
            background-image: url('{{ public_path('uploads/logo.png') }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
    </style>
</head>

<body>
    <!-- Watermark -->
    <div class="watermark"></div>

    <div class="header">
        <div class="content-left-margin">
            <div style="text-align: center; margin-bottom: 20pt; margin-top: 30pt;">
                <img src="{{ public_path('uploads/logo.png') }}" alt="University Logo"
                    style="width: 100px; height: 100px; object-fit: contain;" />
            </div>
            <h1>UNIVERSITY OF MAIDUGURI</h1>
            <h2>...Centre of Excellence</h2>
            <p class="s3 center-align">P.M.B. 1069, MAIDUGURI, NIGERIA</p>
            <table
                style="width: 100%; padding-top: 3pt; padding-left: 5pt; padding-right: 5pt; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: top; padding: 0; margin: 0;">
                        <p class="s2" style="margin: 0;">REGISTRAR:</p>
                        <p class="s3" style="margin: 0;">MAL. AHMAD ALHAJI LAWAN</p>
                    </td>
                    <td style="text-align: right; vertical-align: top; padding: 0; margin: 0;">
                        <p class="s3" style="margin: 0;">Tel: +234 80 6608 0044</p>
                        <p class="s3" style="margin: 0;">Email: registrar@unimaid.edu.ng</p>
                    </td>
                </tr>
            </table>

        </div>
        {{-- line --}}
        <div class="line"></div>
        <div class="line"></div>
        <div class="content-left-margin">
            <p class="s5 center-align">OFFICE OF THE REGISTRAR</p>
            <table
                style="width: 100%; padding-top: 12pt; padding-left: 5pt; padding-right: 5pt; border-collapse: collapse;">
                <tr>
                    <td class="s3" style="text-align: left; padding: 0; margin: 0;">{{ $applicant->fullname }}</td>
                    <td class="s3" style="text-align: right; padding: 0; margin: 0;">
                        {{ date('d F, Y', strtotime($applicant->admission_date)) }}</td>
                </tr>
            </table>
            <table
                style="width: 100%; padding-top: 0; padding-left: 5pt; padding-right: 5pt; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; padding: 0; margin: 0;">{{ $applicant->username }}</td>
                    {{-- UTME001970 use user_id the lenght always UTME0000000 --}}
                    @php
                        $user_id = $applicant->user_id;
                        $length = strlen($user_id);
                        $diff = 7 - $length;
                        $user_id = str_pad($user_id, 7, '0', STR_PAD_LEFT);
                    @endphp
                    <td
                        style="text-align: right; padding: 0; margin: 0; font-size: 9pt; font-weight: normal; color: #666;">
                        UTME{{ $user_id }}</td>
                </tr>
            </table>
            <p style="padding-top: 8pt;"><br /></p>
            <p class="s3 center-align">OFFER OF PROVISIONAL ADMISSION INTO FIRST DEGREE PROGRAMME
                {{ $applicant->mode == 'UTME' ? 'UTME' : 'DE' }} CANDIDATE</p>
            <p style="padding-top: 8pt;"><br /></p>
            <p class="s3 center-align">{{ DB::table('program')->where('code', $applicant->program)->value('award') }}
                {{ DB::table('program')->where('code', $applicant->program)->value('title') }}</p>
            <p style="padding-top: 9pt;"><br /></p>
            <p class="content">I am pleased to inform you that you have been offered a provisional admission into
                {{ $applicant->mode == 'UTME' ? 'Part One (1)' : 'Part Two (2)' }}
                of the above-named undergraduate programme of this University. Details of registration and fees will be
                provided to you at the time of registration.</p>
            <p style="padding-top: 4pt;"><br /></p>
            <p class="content">Your admission is subject to obtaining the minimum entry requirements for the programme
                and
                approval by JAMB.</p>
            <p style="padding-top: 4pt;"><br /></p>
            <p class="content">You are required to present the originals of all credentials at the time of registration.
                Note that if it is discovered at any time that you do not possess any of the qualifications which you
                claimed to have obtained or any of the information you provided is false, you will be required to
                withdraw
                from the University.</p>

            <p style="padding-top: 6pt;"><br /></p>
            <p class="content">Request for change of course will not be entertained.</p>
            {{-- Signature and QR Code section --}}
            <table
                style="width: 100%; padding-top: 6pt; padding-left: 5pt; padding-right: 5pt; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle; padding: 0; margin: 0; width: 70%;">
                        <img src="{{ public_path('uploads/admission.png') }}" alt="" style="width: 100px;">
                        <p class="s6" style="margin: 0; padding-bottom: 2pt;">DR. E. A. FELIX.</p>
                        <p class="s6" style="margin: 0; padding-bottom: 2pt;">Deputy Registrar (Admissions)</p>
                        <p class="s6" style="margin: 0;">For: Registrar</p>
                    </td>
                    <td style="text-align: right; vertical-align: middle; padding: 0; margin: 0; width: 30%;">
                        <div style="text-align: right;">
                            @php
                                $programme = DB::table('program')->where('code', $applicant->program)->value('title');
                                $qrData =
                                    'UNIMAID Admission Letter' .
                                    PHP_EOL .
                                    "Name: {$applicant->fullname}" .
                                    PHP_EOL .
                                    "JAMB: {$applicant->username}" .
                                    PHP_EOL .
                                    "Programme: {$programme}" .
                                    PHP_EOL .
                                    'Date: ' .
                                    date('d F, Y') .
                                    PHP_EOL .
                                    'Verified: UNIMAID';
                            @endphp
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&ecc=M&data={{ urlencode($qrData) }}"
                                alt="QR Code"
                                style="width: 80px; height: 80px; border: 2px solid #000; padding: 5px;" />
                        </div>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>
