<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Biodata and Course Registration - {{ $student->username }}</title>
    <style>
        @page { margin: 22px 25px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #263746; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; line-height: 1.45; }
        .watermark { position: fixed; top: 26%; left: 5%; width: 90%; height: 52%; text-align: center; color: #0b527b; opacity: .10; z-index: 0; }
        .watermark img { width: 275px; height: 275px; object-fit: contain; display: block; margin: 0 auto; position: relative; z-index: 1; }
        .watermark-texts { position: absolute; top: 82px; left: -20%; width: 140%; transform: rotate(-32deg); z-index: 0; }
        .watermark-line { display: block; font-size: 21px; font-weight: bold; letter-spacing: 2.6px; line-height: 1.9; white-space: nowrap; }
        .header { width: 100%; border-bottom: 2px solid #008ed6; padding-bottom: 9px; margin-bottom: 10px; }
        .header td { vertical-align: middle; }
        .logo { width: 62px; height: 62px; object-fit: contain; }
        .header-title { text-align: center; color: #0b4770; }
        .header-title h1 { margin: 0; font-size: 18px; letter-spacing: .2px; }
        .header-title p { margin: 2px 0 0; color: #5a7180; font-size: 8px; }
        .photo-frame { width: 78px; height: 96px; border: 1px solid #cbdbe4; border-radius: 5px; overflow: hidden; text-align: center; background: #f7fafc; }
        .photo-frame img { width: 100%; height: 100%; object-fit: cover; }
        .photo-placeholder { color: #8ca1ad; padding-top: 36px; font-size: 8px; }
        .document-title { padding: 7px 10px; margin: 0 0 8px; background: #008ed6; color: #fff; border-radius: 4px; text-align: center; font-size: 14px; font-weight: bold; letter-spacing: .4px; }
        .identity-bar { width: 100%; margin-bottom: 11px; border: 1px solid #b9d9e8; background: #eef8fd; }
        .identity-bar td { padding: 6px 8px; }
        .identity-label { display: block; color: #567180; font-size: 7px; text-transform: uppercase; letter-spacing: .4px; }
        .identity-value { color: #123f5b; font-size: 10px; font-weight: bold; }
        .section { margin: 0 0 10px; page-break-inside: avoid; }
        .section-title { padding: 5px 8px; margin-bottom: 0; border-radius: 4px 4px 0 0; background: #e8f5fb; border-left: 4px solid #008ed6; color: #0b527b; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; border: 1px solid #dce8ee; }
        .info-table td { width: 50%; padding: 5px 7px; border-bottom: 1px solid #edf2f5; vertical-align: top; }
        .info-table tr:last-child td { border-bottom: 0; }
        .field-label { display: block; color: #6a7f8b; font-size: 7px; text-transform: uppercase; letter-spacing: .25px; }
        .field-value { display: block; margin-top: 1px; color: #233b4a; font-size: 9px; font-weight: bold; word-wrap: break-word; }

        .check-table { width: 100%; border-collapse: collapse; border: 1px solid #dce8ee; }
        .check-table td { width: 33.33%; padding: 6px 8px; border-bottom: 1px solid #edf2f5; }
        .check { color: #198754; font-weight: bold; }
        .missing { color: #bd5b00; font-weight: bold; }
        .hostel-empty { color: #718693; font-style: italic; }
        .course-table { width: 100%; border-collapse: collapse; margin-bottom: 7px; border: 1px solid #cbdde6; }
        .course-table th { padding: 5px 4px; background: #0b527b; color: #fff; font-size: 8px; text-align: left; }
        .course-table td { padding: 4px; border-bottom: 1px solid #e7eef2; font-size: 8px; }
        .course-table tr:nth-child(even) td { background: #f7fbfd; }
        .course-table .num { width: 5%; text-align: center; }
        .course-table .unit { width: 8%; text-align: center; }
        .course-total { margin: -3px 0 8px; color: #0b527b; font-size: 8px; font-weight: bold; text-align: right; }
        .semester-title { margin: 8px 0 4px; color: #0b527b; font-size: 9px; font-weight: bold; }
        .signature-section { margin-top: 12px; page-break-inside: avoid; }
        .signature-table { width: 100%; border-collapse: collapse; border: 1px solid #dce8ee; }
        .signature-table td { width: 33.33%; height: 78px; padding: 8px 9px 6px; text-align: center; vertical-align: bottom; }
        .signature-table img { max-width: 125px; max-height: 38px; display: block; margin: 0 auto 4px; }
        .signature-spacer { height: 42px; }
        .signature-line { border-top: 1px solid #758994; width: 90%; margin: 0 auto; padding-top: 3px; color: #647783; font-size: 7px; text-align: center; }
        .footer { margin-top: 14px; border-top: 1px solid #dce8ee; padding-top: 6px; color: #778b96; font-size: 7px; text-align: center; }
    </style>
</head>

<body>
    <div class="watermark" aria-hidden="true">
        @if ($logoData)
            <img src="{{ $logoData }}" alt="">
        @endif
        <div class="watermark-texts">
            <div class="watermark-line">FOR OFFICIAL USE ONLY</div>
            <div class="watermark-line">FOR OFFICIAL USE ONLY</div>
            <div class="watermark-line">FOR OFFICIAL USE ONLY</div>
        </div>
    </div>

    <table class="header">
        <tr>
            <td style="width:18%;">
                @if ($logoData)
                    <img class="logo" src="{{ $logoData }}" alt="University logo">
                @endif
            </td>
            <td class="header-title" style="width:64%;">
                <h1>UNIVERSITY OF MAIDUGURI</h1>
                <p>STUDENT AFFAIRS - OFFICIAL STUDENT RECORD</p>
                <p>P.M.B. 1069, Maiduguri, Borno State, Nigeria</p>
            </td>
            <td style="width:18%; text-align:right;">
                <div class="photo-frame">
                    @if ($photoData)
                        <img src="{{ $photoData }}" alt="Student photograph">
                    @else
                        <div class="photo-placeholder">NO PHOTO</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="document-title">STUDENT BIODATA / COURSE REGISTRATION</div>

    <table class="identity-bar">
        <tr>
            <td style="width:25%;"><span class="identity-label">Student ID</span><span class="identity-value">{{ $student->username ?: 'Not provided' }}</span></td>
            <td style="width:25%;"><span class="identity-label">Full name</span><span class="identity-value">{{ $fullName ?: 'Not provided' }}</span></td>
            <td style="width:25%;"><span class="identity-label">Current level</span><span class="identity-value">{{ $student->level ?: 'Not provided' }}</span></td>
            <td style="width:25%;"><span class="identity-label">Current session</span><span class="identity-value">{{ $currentSession ?: 'Not provided' }}</span></td>
        </tr>
    </table>

    @foreach ($sections as $title => $fields)
        <section class="section">
            <div class="section-title">{{ $title }}</div>
            <table class="info-table">
                @foreach (array_chunk($fields, 2) as $fieldPair)
                    <tr>
                        @foreach ($fieldPair as $field)
                            <td><span class="field-label">{{ $field['label'] }}</span><span class="field-value">{{ $field['value'] }}</span></td>
                        @endforeach
                        @if (count($fieldPair) === 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </section>
    @endforeach


    <section class="section">
        <div class="section-title">Hostel allocation</div>
        <table class="info-table">
            <tr>
                <td><span class="field-label">Hall</span><span class="field-value">{{ $hostel->hall ?? 'Not allocated' }}</span></td>
                <td><span class="field-label">Block</span><span class="field-value">{{ $hostel->block ?? 'Not allocated' }}</span></td>
            </tr>
            <tr>
                <td><span class="field-label">Room</span><span class="field-value">{{ $hostel->room ?? 'Not allocated' }}</span></td>
                <td><span class="field-label">Bed</span><span class="field-value">{{ $hostel->bed ?? 'Not allocated' }}</span></td>
            </tr>
        </table>
    </section>


    @if ($currentCourseCount > 0)
        <section class="section">
            <div class="section-title">Current session course registration - {{ $currentSession }}</div>
            <p style="margin:5px 0 7px; color:#5f7581;">The following courses were registered for the current session.</p>
            @foreach ($courseGroups as $semester => $courses)
                <div class="semester-title">{{ $semester === 'FIRST' ? 'First semester' : ($semester === 'SECOND' ? 'Second semester' : ucfirst(strtolower($semester))) }}</div>
                <table class="course-table">
                    <thead><tr><th class="num">No.</th><th>Code</th><th>Course title</th><th class="unit">Unit</th><th>Level</th><th>Type</th></tr></thead>
                    <tbody>
                        @foreach ($courses as $index => $course)
                            <tr>
                                <td class="num">{{ $index + 1 }}</td>
                                <td>{{ $course->code }}</td>
                                <td>{{ $course->course_title }}</td>
                                <td class="unit">{{ $course->unit }}</td>
                                <td>{{ $course->level ?: '-' }}</td>
                                <td>{{ $course->type ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="course-total">Total units: {{ $courses->sum('unit') }}</div>
            @endforeach
        </section>
    @endif

    <section class="signature-section">
        <div class="section-title">Required signatures</div>
        <table class="signature-table">
            <tr>
                <td>
                    @if ($signatureData)
                        <img src="{{ $signatureData }}" alt="Student signature">
                    @endif
                    <div class="signature-line">Student's signature</div>
                </td>
                <td>
                    <div class="signature-spacer"></div>
                    <div class="signature-line">HOD's signature</div>
                </td>
                <td>
                    <div class="signature-spacer"></div>
                    <div class="signature-line">Registrar's signature</div>
                </td>
            </tr>
        </table>
    </section>

    <div class="footer">Generated from the University of Maiduguri Student Portal on {{ now()->format('d M Y, h:i A') }}. This document should be kept for official use.</div>
</body>

</html>
