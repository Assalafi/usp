@php
    $personal = $application['personal'] ?? [];
    $contact = $application['contact'] ?? [];
    $job = $application['job'] ?? [];
    $prof = $application['professional'] ?? [];
    $fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['middle_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
    $appNo = $application['application_number'] ?? 'N/A';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CV - {{ $fullName }}</title>
    <style>
        @page { margin: 15mm 15mm 20mm 15mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; line-height: 1.5; }
        h1 { font-size: 22px; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 1px; }
        h2 { font-size: 13px; font-weight: bold; text-transform: uppercase; background-color: #2c3e50; color: #fff; padding: 5px 10px; margin: 18px 0 8px 0; }
        h3 { font-size: 11px; font-weight: bold; margin: 10px 0 4px 0; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; padding: 0; }
        .info-table { margin-bottom: 4px; }
        .info-table td { padding: 3px 8px; vertical-align: top; font-size: 11px; }
        .info-table .label { font-weight: bold; width: 160px; color: #444; }
        .info-table .value { color: #222; }
        .two-col-table td { width: 50%; vertical-align: top; padding-right: 10px; }
        .edu-table, .exp-table, .ref-table { width: 100%; margin-bottom: 2px; }
        .edu-table td, .exp-table td, .ref-table td { padding: 3px 8px; font-size: 11px; vertical-align: top; }
        .edu-table tr:nth-child(odd), .exp-table tr:nth-child(odd) { background-color: #f7f7f7; }
        .separator { border-bottom: 2px solid #2c3e50; margin-bottom: 10px; }
        .sub-separator { border-bottom: 1px solid #ddd; margin: 6px 0; }
        .photo-cell { width: 100px; text-align: right; }
        .photo-cell img { width: 95px; height: 110px; border: 1px solid #ccc; }
        .name-cell { padding-right: 10px; }
        .tag { display: inline; background-color: #ecf0f1; padding: 2px 8px; font-size: 10px; border-radius: 3px; color: #555; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; }
        .page-break { page-break-before: always; }
        .doc-list-table { width: 100%; }
        .doc-list-table td { padding: 4px 8px; font-size: 11px; border-bottom: 1px solid #eee; }
        .doc-list-table .doc-num { width: 30px; font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>

{{-- ==================== HEADER ==================== --}}
<table class="header-table">
    <tr>
        <td class="name-cell">
            <h1>{{ $fullName ?: 'Applicant' }}</h1>
            <div class="separator"></div>
            <table class="info-table">
                <tr>
                    <td class="label">Application No:</td>
                    <td class="value">{{ $appNo }}</td>
                </tr>
                <tr>
                    <td class="label">Position Applied:</td>
                    <td class="value"><strong>{{ $job['title'] ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Department:</td>
                    <td class="value">{{ $job['department_name'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value">{{ $contact['contact_email'] ?? $applicant['email'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td class="value">{{ $contact['contact_phone'] ?? $applicant['phone'] ?? 'N/A' }}</td>
                </tr>
            </table>
        </td>
        @if(!empty($application['passport_photo']))
        <td class="photo-cell">
            <img src="{{ $application['passport_photo'] }}" alt="Photo" />
        </td>
        @endif
    </tr>
</table>

{{-- ==================== PERSONAL INFORMATION ==================== --}}
<h2>Personal Information</h2>
<table class="two-col-table">
    <tr>
        <td>
            <table class="info-table">
                <tr><td class="label">Full Name:</td><td class="value">{{ $fullName ?: 'N/A' }}</td></tr>
                <tr><td class="label">Date of Birth:</td><td class="value">{{ $personal['date_of_birth'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">Place of Birth:</td><td class="value">{{ $personal['place_of_birth'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">Gender:</td><td class="value">{{ $personal['gender'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">Marital Status:</td><td class="value">{{ $personal['marital_status'] ?? 'N/A' }}</td></tr>
            </table>
        </td>
        <td>
            <table class="info-table">
                <tr><td class="label">Nationality:</td><td class="value">{{ $personal['nationality'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">State of Origin:</td><td class="value">{{ $personal['state_of_origin'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">LGA:</td><td class="value">{{ $personal['local_govt_of_origin'] ?? 'N/A' }}</td></tr>
                @if(!empty($personal['nin']))
                <tr><td class="label">NIN:</td><td class="value">{{ $personal['nin'] }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- ==================== CONTACT INFORMATION ==================== --}}
<h2>Contact Information</h2>
<table class="info-table">
    <tr><td class="label">Email:</td><td class="value">{{ $contact['contact_email'] ?? $applicant['email'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Phone:</td><td class="value">{{ $contact['contact_phone'] ?? $applicant['phone'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Permanent Address:</td><td class="value">{{ $contact['permanent_home_address'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Current Address:</td><td class="value">{{ $contact['current_postal_address'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">City:</td><td class="value">{{ $contact['city'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Country:</td><td class="value">{{ $contact['country'] ?? 'N/A' }}</td></tr>
</table>

{{-- ==================== POSITION APPLIED FOR ==================== --}}
<h2>Position Applied For</h2>
<table class="info-table">
    <tr><td class="label">Position:</td><td class="value"><strong>{{ $job['title'] ?? 'N/A' }}</strong></td></tr>
    <tr><td class="label">Department:</td><td class="value">{{ $job['department_name'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Staff Type:</td><td class="value">{{ $job['staff_type'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Job Type:</td><td class="value">{{ $job['job_type'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Experience Level:</td><td class="value">{{ $job['experience_level'] ?? 'N/A' }}</td></tr>
</table>

{{-- ==================== PROFESSIONAL INFORMATION ==================== --}}
<h2>Professional Summary</h2>
<table class="info-table">
    <tr><td class="label">Employment Status:</td><td class="value">{{ $prof['employment_status'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Years of Experience:</td><td class="value">{{ ($prof['experience_years'] ?? '0') }} years</td></tr>
</table>
@if(!empty($prof['extra_curricular_activities']))
    <h3>Extra Curricular Activities</h3>
    <p style="margin: 2px 0 0 10px; font-size: 11px;">{{ $prof['extra_curricular_activities'] }}</p>
@endif

{{-- ==================== EDUCATION ==================== --}}
@if(!empty($application['education']))
<h2>Educational Background</h2>
<table class="edu-table">
    <tr style="background-color: #2c3e50; color: #fff;">
        <td style="padding: 5px 8px; font-weight: bold; width: 30px;">#</td>
        <td style="padding: 5px 8px; font-weight: bold;">Institution</td>
        <td style="padding: 5px 8px; font-weight: bold;">Qualification</td>
        <td style="padding: 5px 8px; font-weight: bold;">Field of Study</td>
        <td style="padding: 5px 8px; font-weight: bold; width: 60px;">Year</td>
    </tr>
    @foreach($application['education'] as $index => $edu)
    <tr>
        <td style="padding: 5px 8px;">{{ $index + 1 }}</td>
        <td style="padding: 5px 8px;">{{ $edu['institution'] ?? 'N/A' }}</td>
        <td style="padding: 5px 8px;">{{ $edu['degree'] ?? 'N/A' }}</td>
        <td style="padding: 5px 8px;">{{ $edu['field_of_study'] ?? 'N/A' }}</td>
        <td style="padding: 5px 8px;">{{ $edu['graduation_year'] ?? 'N/A' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- ==================== WORK EXPERIENCE ==================== --}}
@if(!empty($application['work_experience']))
<h2>Work Experience</h2>
<table class="exp-table">
    <tr style="background-color: #2c3e50; color: #fff;">
        <td style="padding: 5px 8px; font-weight: bold; width: 30px;">#</td>
        <td style="padding: 5px 8px; font-weight: bold;">Position</td>
        <td style="padding: 5px 8px; font-weight: bold;">Organization</td>
        <td style="padding: 5px 8px; font-weight: bold; width: 120px;">Period</td>
    </tr>
    @foreach($application['work_experience'] as $index => $work)
    <tr>
        <td style="padding: 5px 8px;">{{ $index + 1 }}</td>
        <td style="padding: 5px 8px; font-weight: bold;">{{ $work['position'] ?? 'N/A' }}</td>
        <td style="padding: 5px 8px;">{{ $work['place'] ?? 'N/A' }}</td>
        <td style="padding: 5px 8px;">{{ $work['date'] ?? 'N/A' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- ==================== REFEREES ==================== --}}
@if(!empty($application['referees']))
<h2>Referees</h2>
<table class="ref-table">
    @foreach($application['referees'] as $index => $referee)
    <tr>
        <td style="padding: 6px 8px; width: 30px; font-weight: bold; vertical-align: top; color: #2c3e50;">{{ $index + 1 }}.</td>
        <td style="padding: 6px 8px;">
            <strong>{{ $referee['name'] ?? 'N/A' }}</strong><br>
            {{ $referee['address'] ?? '' }}
            @if(!empty($referee['phone'])) | Phone: {{ $referee['phone'] }} @endif
            @if(!empty($referee['email'])) | Email: {{ $referee['email'] }} @endif
        </td>
    </tr>
    @if(!$loop->last)
    <tr><td colspan="2"><div class="sub-separator"></div></td></tr>
    @endif
    @endforeach
</table>
@endif

{{-- ==================== DOCUMENTS LIST ==================== --}}
@if(!empty($application['documents']))
<h2>Submitted Documents</h2>
<table class="doc-list-table">
    @foreach($application['documents'] as $index => $doc)
    <tr>
        <td class="doc-num">{{ $index + 1 }}.</td>
        <td><strong>{{ $doc['label'] ?? 'Document' }}</strong></td>
        <td style="text-align: right; color: #888; font-size: 10px;">{{ $doc['file_name'] ?? '' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    This CV was generated on {{ date('d F Y \a\t H:i:s') }} | Application #{{ $appNo }}
</div>

</body>
</html>