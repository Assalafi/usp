@php
    use Illuminate\Support\Facades\DB;
@endphp
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SIWES Information - {{ $student->username }} - UNIMAID</title>
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
            line-height: 0.7;
        }

        .content-left-margin {
            margin-left: 30px;
            margin-right: 30px;
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

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7pt;
        }

        .info-table td {
            padding: 7pt 5pt;
            vertical-align: top;
            border-bottom: 1px solid black;
        }

        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .photo-container {
            text-align: center;
            margin: 20pt 0;
        }

        .student-photo {
            width: 100px;
            height: 100px;
            border: 2px solid black;
            object-fit: cover;
        }

        .no-photo {
            width: 100px;
            height: 100px;
            border: 2px solid black;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
            color: #999;
        }
    </style>
</head>

<body>
    <!-- Watermark -->
    <div class="watermark"></div>

    <div class="header">
        <div class="content-left-margin">
            <table style="width: 100%; margin-bottom: 10pt; margin-top: 10pt; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle; padding: 0; margin: 0;">
                        <img src="{{ public_path('uploads/logo.png') }}" alt="University Logo"
                            style="width: 100px; height: 100px; object-fit: contain;" />
                    </td>
                    <td style="text-align: right; vertical-align: middle; padding: 0; margin: 0;">
                        @if($student->picture && file_exists(public_path('storage/picture/' . $student->picture)))
                            <img src="{{ public_path('storage/picture/' . $student->picture) }}" alt="Student Photo" class="student-photo" />
                        @else
                            <div class="no-photo">No Photo</div>
                        @endif
                    </td>
                </tr>
            </table>
            <h1>UNIVERSITY OF MAIDUGURI</h1>
            <h2>...Centre of Excellence</h2>
            <p class="s3 center-align">P.M.B. 1069, MAIDUGURI, NIGERIA</p>
            <p class="s5 center-align">SIWES INFORMATION FORM</p>
            <p class="s3 center-align">Student Industrial Work Experience Scheme</p>
        </div>
        
        <div class="line"></div>
        <div class="line"></div>
        
        <div class="content-left-margin">

            <p style="padding-top: 3pt;"><br /></p>
            <p class="s3">STUDENT DETAILS</p>
            
            <table class="info-table">
                <tr>
                    <td>Name</td>
                    <td>{{ strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name) }}</td>
                </tr>
                <tr>
                    <td>Matric Number</td>
                    <td>{{ strtoupper($student->username) }}</td>
                </tr>
                <tr>
                    <td>Course of Study</td>
                    <td>{{ strtoupper(DB::table('program')->where('code', $student->program)->value('title')) }}</td>
                </tr>
                <tr>
                    <td>Level of Study</td>
                    <td>{{ $student->level }} LEVEL</td>
                </tr>
            </table>

            <p style="padding-top: 5pt;"><br /></p>
            <p class="s3">SIWES DETAILS</p>
            
            <table class="info-table">
                <tr>
                    <td>Period of Attachment (From)</td>
                    <td>{{ date('d F Y', strtotime($siwesData->period_of_attachment_from)) }}</td>
                </tr>
                <tr>
                    <td>Period of Attachment (To)</td>
                    <td>{{ date('d F Y', strtotime($siwesData->period_of_attachment_to)) }}</td>
                </tr>
                <tr>
                    <td>Placement Address</td>
                    <td>{{ strtoupper($siwesData->placement_of_address) }}</td>
                </tr>
                <tr>
                    <td>SIWES Year</td>
                    <td>{{ $siwesData->siwes_year }}</td>
                </tr>
            </table>

            <p style="padding-top: 5pt;"><br /></p>
            <p class="s3">BANK DETAILS</p>
            
            <table class="info-table">
                <tr>
                    <td>Bank Name</td>
                    <td>{{ strtoupper($siwesData->bank_name) }}</td>
                </tr>
                <tr>
                    <td>Bank Code</td>
                    <td>{{ $siwesData->bank_code }}</td>
                </tr>
                <tr>
                    <td>Account Number</td>
                    <td>{{ $siwesData->account_number }}</td>
                </tr>
                <tr>
                    <td>Sort Code</td>
                    <td>{{ $siwesData->sort_code ?? 'N/A' }}</td>
                </tr>
            </table>

            <p style="padding-top: 5pt;"><br /></p>
            <p class="s3">CONTACT INFORMATION</p>
            
            <table class="info-table">
                <tr>
                    <td>Student Email Address</td>
                    <td>{{ strtolower($siwesData->student_email_address) }}</td>
                </tr>
                <tr>
                    <td>Remarks</td>
                    <td>{{ strtoupper($siwesData->remarks ?? 'N/A') }}</td>
                </tr>
            </table>

            <p style="padding-top: 10pt;"><br /></p>
            <p class="content">Date: {{ date('d F Y') }}</p>

        </div>
    </div>
</body>

</html>
