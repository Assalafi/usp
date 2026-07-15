@php
    $fullName = $row->name ?? 'Staff';
    $username = $row->username ?? 'N/A';
    $promotions = json_decode($row->promotions ?? '[]', true) ?: [];
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
        .edu-table, .exp-table { width: 100%; margin-bottom: 2px; }
        .edu-table td, .exp-table td { padding: 3px 8px; font-size: 11px; vertical-align: top; }
        .edu-table tr:nth-child(odd), .exp-table tr:nth-child(odd) { background-color: #f7f7f7; }
        .separator { border-bottom: 2px solid #2c3e50; margin-bottom: 10px; }
        .sub-separator { border-bottom: 1px solid #ddd; margin: 6px 0; }
        .photo-cell { width: 100px; text-align: right; }
        .photo-cell img { width: 95px; height: 110px; border: 1px solid #ccc; }
        .name-cell { padding-right: 10px; }
        .tag { display: inline; background-color: #ecf0f1; padding: 2px 8px; font-size: 10px; border-radius: 3px; color: #555; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; }
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
            <h1>{{ $fullName ?: 'Staff' }}</h1>
            <div class="separator"></div>
            <table class="info-table">
                <tr>
                    <td class="label">Staff ID:</td>
                    <td class="value">{{ $username }}</td>
                </tr>
                <tr>
                    <td class="label">Current Rank:</td>
                    <td class="value"><strong>{{ $row->current_rank ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Unit/Department:</td>
                    <td class="value">{{ $unitName ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value">{{ $row->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td class="value">{{ $row->phone ?? 'N/A' }}</td>
                </tr>
            </table>
        </td>
        @if(!empty($photoDataUri))
        <td class="photo-cell">
            <img src="{{ $photoDataUri }}" alt="Photo" />
        </td>
        @elseif($row->picture && file_exists(public_path('storage/picture/' . $row->picture)))
        <td class="photo-cell">
            <img src="{{ public_path('storage/picture/' . $row->picture) }}" alt="Photo" />
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
                <tr><td class="label">Gender:</td><td class="value">{{ $row->gender ?? 'N/A' }}</td></tr>
                <tr><td class="label">Marital Status:</td><td class="value">{{ $row->marital_status ?? 'N/A' }}</td></tr>
                <tr><td class="label">Date of Birth:</td><td class="value">{{ ($row->date_of_birth && $row->date_of_birth != '1970-01-01') ? $row->date_of_birth : 'N/A' }}</td></tr>
            </table>
        </td>
        <td>
            <table class="info-table">
                <tr><td class="label">State of Origin:</td><td class="value">{{ $row->state ?? 'N/A' }}</td></tr>
                <tr><td class="label">LGA:</td><td class="value">{{ $row->lga ?? 'N/A' }}</td></tr>
                <tr><td class="label">Nationality:</td><td class="value">{{ $row->nationality ?? 'N/A' }}</td></tr>
                <tr><td class="label">NIN:</td><td class="value">{{ $row->nin ?? 'N/A' }}</td></tr>
                <tr><td class="label">Phone:</td><td class="value">{{ $row->phone ?? 'N/A' }}</td></tr>
                <tr><td class="label">Email:</td><td class="value">{{ $row->email ?? 'N/A' }}</td></tr>
            </table>
        </td>
    </tr>
</table>
@if(!empty($row->address))
<h3>Home Address</h3>
<p style="margin: 2px 0 0 10px; font-size: 11px;">{{ $row->address }}</p>
@endif

{{-- ==================== SERVICE RECORD ==================== --}}
<h2>Service Record</h2>
<table class="info-table">
    <tr><td class="label">Staff Category:</td><td class="value">{{ $row->staff_category ?? 'N/A' }}</td></tr>
    <tr><td class="label">Employment Status:</td><td class="value">{{ $row->employee_status ?? 'N/A' }}</td></tr>
    <tr><td class="label">Grade/Level:</td><td class="value">{{ $gradeName ?? $row->grade ?? 'N/A' }}</td></tr>
    <tr><td class="label">Step:</td><td class="value">{{ $stepName ?? $row->step ?? 'N/A' }}</td></tr>
    <tr><td class="label">Rank on First Appointment:</td><td class="value">{{ $row->rank_of_first_appointment ?? 'N/A' }}</td></tr>
    <tr><td class="label">Date of First Appointment:</td><td class="value">{{ ($row->date_of_first_appointment && $row->date_of_first_appointment != '1970-01-01') ? $row->date_of_first_appointment : 'N/A' }}</td></tr>
    <tr><td class="label">Date of Assumption:</td><td class="value">{{ ($row->date_of_asumption && $row->date_of_asumption != '1970-01-01') ? $row->date_of_asumption : 'N/A' }}</td></tr>
    <tr><td class="label">Date of Confirmation:</td><td class="value">{{ ($row->date_of_comfirmation && $row->date_of_comfirmation != '1970-01-01') ? $row->date_of_comfirmation : 'N/A' }}</td></tr>
    <tr><td class="label">Current Qualification recognized by the university:</td><td class="value">{{ $row->current_qualification ?? 'N/A' }}</td></tr>
    <tr><td class="label">Staff Status:</td><td class="value">{{ $row->staff_status ?? 'Active' }}</td></tr>
    @if($row->staff_status && $row->staff_status != 'Active')
        <tr><td class="label">Institution/Organization:</td><td class="value">{{ $row->leave_institution ?? 'N/A' }}</td></tr>
        <tr><td class="label">Leave Start Date:</td><td class="value">{{ ($row->leave_start_date && $row->leave_start_date != '1970-01-01') ? $row->leave_start_date : 'N/A' }}</td></tr>
        <tr><td class="label">Leave End Date:</td><td class="value">{{ ($row->leave_end_date && $row->leave_end_date != '1970-01-01') ? $row->leave_end_date : 'N/A' }}</td></tr>
    @endif
    <tr><td class="label">Years of Experience:</td><td class="value">{{ $row->year_of_experiance ?? 'N/A' }}</td></tr>
</table>

@if(!empty($promotions))
<h2>Promotions</h2>
@foreach($promotions as $promo)
<table class="info-table">
    <tr><td class="label">{{ $promo['promotion'] ?? '' }} Promotion - Date:</td><td class="value">{{ $promo['date'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Designation:</td><td class="value">{{ $promo['designation'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Grade:</td><td class="value">{{ $promo['grade'] ?? 'N/A' }}</td></tr>
    <tr><td class="label">Step:</td><td class="value">{{ $promo['step'] ?? 'N/A' }}</td></tr>
</table>
@endforeach
@endif

{{-- ==================== ACADEMIC INFORMATION ==================== --}}
<h2>Academic Information</h2>
<table class="info-table">
    <tr><td class="label">Faculty:</td><td class="value">{{ $row->faculty ?? 'N/A' }}</td></tr>
    <tr><td class="label">Department:</td><td class="value">{{ $row->department ?? 'N/A' }}</td></tr>
    <tr><td class="label">Program:</td><td class="value">{{ $row->program ?? 'N/A' }}</td></tr>
    <tr><td class="label">Degree Status:</td><td class="value">{{ $row->degree ? 'Available' : 'Not Available' }}</td></tr>
</table>

{{-- Education / Institutions --}}
@if(!empty($institutions))
<h2>Educational Qualifications</h2>
<table class="edu-table">
    <tr style="background-color: #2c3e50; color: #fff;">
        <td style="padding: 5px 8px; font-weight: bold; width: 30px;">#</td>
        <td style="padding: 5px 8px; font-weight: bold;">Institution</td>
        <td style="padding: 5px 8px; font-weight: bold;">Degree</td>
        <td style="padding: 5px 8px; font-weight: bold;">Field of Study</td>
        <td style="padding: 5px 8px; font-weight: bold; width: 60px;">Year</td>
    </tr>
    @foreach($institutions as $index => $inst)
    <tr>
        <td style="padding: 5px 8px;">{{ $index + 1 }}</td>
        <td style="padding: 5px 8px;">{{ is_array($inst) ? ($inst['name'] ?? 'N/A') : $inst }}</td>
        <td style="padding: 5px 8px;">{{ is_array($inst) ? ($inst['degree'] ?? 'N/A') : '' }}</td>
        <td style="padding: 5px 8px;">{{ is_array($inst) ? ($inst['field'] ?? 'N/A') : '' }}</td>
        <td style="padding: 5px 8px;">{{ is_array($inst) ? ($inst['year'] ?? 'N/A') : '' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- Work Experience --}}
@if(!empty($experiences))
<h2>Work Experience</h2>
<table class="exp-table">
    <tr style="background-color: #2c3e50; color: #fff;">
        <td style="padding: 5px 8px; font-weight: bold; width: 30px;">#</td>
        <td style="padding: 5px 8px; font-weight: bold;">Position</td>
        <td style="padding: 5px 8px; font-weight: bold;">Organization</td>
        <td style="padding: 5px 8px; font-weight: bold; width: 120px;">Period</td>
    </tr>
    @foreach($experiences as $index => $exp)
    <tr>
        <td style="padding: 5px 8px;">{{ $index + 1 }}</td>
        <td style="padding: 5px 8px; font-weight: bold;">{{ is_array($exp) ? ($exp['position'] ?? 'N/A') : $exp }}</td>
        <td style="padding: 5px 8px;">{{ is_array($exp) ? ($exp['place'] ?? 'N/A') : '' }}</td>
        <td style="padding: 5px 8px;">{{ is_array($exp) ? ($exp['date'] ?? 'N/A') : '' }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- Publications --}}
@if(!empty($publications) && $publications != [''])
<h2>Publications</h2>
<ul style="margin: 4px 0 0 16px; padding: 0; font-size: 11px;">
    @foreach($publications as $pub)
        @if(!empty($pub))
            <li>{{ is_array($pub) ? (isset($pub['title']) ? $pub['title'] : implode(' ', array_filter(array_values($pub)))) : $pub }}</li>
        @endif
    @endforeach
</ul>
@endif

{{-- Honours --}}
@if(!empty($honours) && $honours != [''])
<h2>Honours / Distinctions</h2>
<ul style="margin: 4px 0 0 16px; padding: 0; font-size: 11px;">
    @foreach($honours as $hon)
        @if(!empty($hon))
            <li>{{ is_array($hon) ? (isset($hon['title']) ? $hon['title'] : implode(' ', array_filter(array_values($hon)))) : $hon }}</li>
        @endif
    @endforeach
</ul>
@endif

{{-- Memberships --}}
@if(!empty($memberships) && $memberships != [''])
<h2>Professional Memberships</h2>
<ul style="margin: 4px 0 0 16px; padding: 0; font-size: 11px;">
    @foreach($memberships as $mem)
        @if(!empty($mem))
            <li>{{ is_array($mem) ? (isset($mem['title']) ? $mem['title'] : implode(' ', array_filter(array_values($mem)))) : $mem }}</li>
        @endif
    @endforeach
</ul>
@endif

{{-- Extra Curricular --}}
@if(!empty($row->extra_curricular))
<h2>Extra-curricular Activities</h2>
<p style="margin: 2px 0 0 10px; font-size: 11px;">{{ $row->extra_curricular }}</p>
@endif

{{-- ==================== NEXT OF KIN ==================== --}}
@if(!empty($row->kin_name) || !empty($row->kin_phone))
<h2>Next of Kin</h2>
<table class="info-table">
    <tr><td class="label">Full Name:</td><td class="value">{{ $row->kin_name ?? 'N/A' }}</td></tr>
    <tr><td class="label">Phone:</td><td class="value">{{ $row->kin_phone ?? 'N/A' }}</td></tr>
    <tr><td class="label">Address:</td><td class="value">{{ $row->kin_address ?? 'N/A' }}</td></tr>
    <tr><td class="label">Relationship:</td><td class="value">{{ $row->kin_relationship ?? 'N/A' }}</td></tr>
</table>
@endif

{{-- ==================== FINANCIAL DETAILS ==================== --}}
@if(!empty($row->bank_name) || !empty($row->account_number) || !empty($row->pension_administrator) || !empty($row->pension_number))
<h2>Financial Details</h2>
<table class="info-table">
    <tr><td class="label">Bank Name:</td><td class="value">{{ $row->bank_name ?? 'N/A' }}</td></tr>
    <tr><td class="label">Account Number:</td><td class="value">{{ $row->account_number ?? 'N/A' }}</td></tr>
    <tr><td class="label">Pension Name:</td><td class="value">{{ $row->pension_administrator ?? 'N/A' }}</td></tr>
    <tr><td class="label">Pension PIN Number:</td><td class="value">{{ $row->pension_number ?? 'N/A' }}</td></tr>
</table>
@endif

{{-- ==================== DOCUMENTS LIST ==================== --}}
@php
    $docMap = [
        'doc_photo' => 'Photo',
        'doc_birth_certificate' => 'Birth Certificate/Declaration of Age',
        'doc_primary_cert' => 'Primary School Certificate',
        'doc_ssce' => 'SSCE/GCE',
        'doc_diploma' => 'Diploma',
        'doc_degree' => 'Degree',
        'doc_masters' => 'Masters',
        'doc_phd' => 'PhD',
        'doc_indigine' => 'Indigene',
        'doc_workshop' => 'Workshop Cert',
        'doc_nysc' => 'NYSC/Exception',
        'doc_appointment_letter' => 'Appointment Letter',
        'doc_confirmation' => 'Letter of Confirmation',
        'doc_professional_body' => 'Certificate of Professional Body Membership',
    ];
    $hasAnyDoc = false;
    foreach ($docMap as $f => $l) { if (!empty($row->$f)) { $hasAnyDoc = true; break; } }
    if (!$hasAnyDoc && !empty($docOthers)) $hasAnyDoc = true;
@endphp
@if($hasAnyDoc)
<h2>Submitted Documents</h2>
<table class="doc-list-table">
    @php $docIndex = 1; @endphp
    @foreach($docMap as $field => $label)
        @if(!empty($row->$field))
        <tr>
            <td class="doc-num">{{ $docIndex }}.</td>
            <td><strong>{{ $label }}</strong></td>
            <td style="text-align: right; color: #888; font-size: 10px;">{{ $row->$field }}</td>
        </tr>
        @php $docIndex++; @endphp
        @endif
    @endforeach
    @if(!empty($docOthers))
        @foreach($docOthers as $other)
        <tr>
            <td class="doc-num">{{ $docIndex }}.</td>
            <td><strong>{{ $other['name'] ?? 'Other Document' }}</strong></td>
            <td style="text-align: right; color: #888; font-size: 10px;">{{ $other['file'] ?? '' }}</td>
        </tr>
        @php $docIndex++; @endphp
        @endforeach
    @endif
</table>
@endif

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    This CV was generated on {{ date('d F Y \a\t H:i:s') }} | Staff ID: {{ $username }}
</div>

</body>
</html>
