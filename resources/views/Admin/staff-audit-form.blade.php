@php
    $fullName = $row->name ?? '';
    $username = $row->username ?? '';
    $dob = (isset($row->date_of_birth) && $row->date_of_birth && $row->date_of_birth != '1970-01-01') ? date('d/m/Y', strtotime($row->date_of_birth)) : '';
    $dofa = (isset($row->date_of_first_appointment) && $row->date_of_first_appointment && $row->date_of_first_appointment != '1970-01-01') ? date('d/m/Y', strtotime($row->date_of_first_appointment)) : '';
    $doc = (isset($row->date_of_comfirmation) && $row->date_of_comfirmation && $row->date_of_comfirmation != '1970-01-01') ? date('d/m/Y', strtotime($row->date_of_comfirmation)) : '';
    $doca = (isset($row->date_of_current_appointment) && $row->date_of_current_appointment && $row->date_of_current_appointment != '1970-01-01') ? date('d/m/Y', strtotime($row->date_of_current_appointment)) : '';
    $accommodation = property_exists($row, 'accommodation_status') ? ($row->accommodation_status ?? '') : '';
    $currentRankSalary = ($designationName ?? '') . (!empty($gradeName) ? ' / ' . $gradeName : '');

    // Embed logo
    $logoPath = public_path('uploads/logo.png');
    $logoDataUri = '';
    if (file_exists($logoPath)) {
        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Embed staff photo
    $photoDataUri = '';
    if (!empty($row->picture)) {
        $picPath = storage_path('app/public/picture/' . $row->picture);
        if (file_exists($picPath)) {
            $ext = strtolower(pathinfo($row->picture, PATHINFO_EXTENSION));
            $mimeMap = ['jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','gif'=>'gif'];
            $mime = $mimeMap[$ext] ?? $ext;
            $photoDataUri = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($picPath));
        }
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Form - {{ $fullName }}</title>
    <style>
        @page { margin: 10mm 12mm 10mm 12mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; line-height: 1.4; }
        .outer-border { border: 3px solid #228B22; padding: 10px 15px; }
        table.header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table.header-table td { vertical-align: middle; padding: 0; }
        .header-center { text-align: center; }
        .header-center h1 { font-size: 14px; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header-center h2 { font-size: 10.5px; margin: 1px 0; font-style: italic; font-weight: normal; }
        .header-center h3 { font-size: 10px; margin: 1px 0; font-style: italic; font-weight: bold; }
        .form-title { text-align: center; font-size: 12px; font-weight: bold; margin: 8px 0 6px 0; }
        .form-title u { text-decoration: underline; }
        table.form-table { width: 100%; border-collapse: collapse; }
        table.form-table td { padding: 4px 2px; vertical-align: bottom; font-size: 11px; }
        .ul { border-bottom: 1px solid #000; }
        .note-section { margin-top: 10px; border-top: 1px solid #000; padding-top: 6px; }
        .note-section p { margin: 0 0 2px 0; font-weight: bold; font-size: 10.5px; }
        .note-section ol { margin: 3px 0 0 0; padding-left: 16px; font-size: 10px; }
        .note-section ol li { margin-bottom: 3px; }
        .warning { text-align: center; font-weight: bold; font-style: italic; margin-top: 8px; font-size: 11px; }
    </style>
</head>
<body>

<div class="outer-border">

{{-- 3-Column Header: Logo | Text | Photo --}}
<table class="header-table">
    <tr>
        <td style="width:80px; text-align:left;">
            @if($logoDataUri)
                <img src="{{ $logoDataUri }}" style="width:70px; height:auto;" />
            @endif
        </td>
        <td class="header-center">
            <h1>UNIVERSITY OF MAIDUGURI</h1>
            <h2>(Office of the Vice-Chancellor)</h2>
            <h3>Internal Audit Unit</h3>
        </td>
        <td style="width:80px; text-align:right;">
            @if($photoDataUri)
                <img src="{{ $photoDataUri }}" style="width:70px; height:85px; object-fit:cover; border:1px solid #ccc;" />
            @endif
        </td>
    </tr>
</table>

<div class="form-title"><u>{{ date('Y') }} STAFF MANPOWER AUDIT FORM</u></div>

<table class="form-table">
    <tr>
        <td colspan="3" style="text-align:right; padding-bottom:8px;">
            <strong>GSM:</strong> <span class="ul" style="padding:0 4px;">{{ $row->phone ?? '' }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="ul">1. Name: <strong>{{ $fullName }}</strong></td>
        <td class="ul">Staff No: <strong>{{ $username }}</strong></td>
    </tr>
    <tr>
        <td class="ul">2. Date of Birth: {{ $dob }}</td>
        <td class="ul">Local Govt.: {{ $row->lga ?? '' }}</td>
        <td class="ul">Town: {{ $row->state ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">3. Nationality: {{ $row->nationality ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2" class="ul">4. Department/Unit: {{ $deptUnitDisplay ?? '' }}</td>
        <td class="ul">E-mail: {{ $row->email ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">5. Employment Category: {{ $row->employee_status ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:center; font-size:10px; font-style:italic; padding:0 0 4px 0;">(Permanent/Contract/Sabbatical/Visiting/Temp)</td>
    </tr>
    <tr>
        <td colspan="2" class="ul">6. Date of First Appointment: {{ $dofa }}</td>
        <td class="ul">Rank on First Appointment: {{ $row->rank_of_first_appointment ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">7. Date of Confirmation of Appointment: {{ $doc }}</td>
    </tr>
    <tr>
        <td colspan="2" class="ul">8. Date of Current Appointment: {{ $doca }}</td>
        <td class="ul">Current Rank/ Salary: {{ $currentRankSalary }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">9. Accommodation Status/House No: {{ $accommodation }}</td>
    </tr>
    <tr>
        <td colspan="2" class="ul">10. Bank Name: {{ $row->bank_name ?? '' }}</td>
        <td class="ul">Account No.: {{ $row->account_number ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">11. Next of Kin: {{ $row->kin_name ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2" class="ul">12. Address of Next of Kin: {{ $row->kin_address ?? '' }}</td>
        <td class="ul">GSM: {{ $row->kin_phone ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="3" class="ul">13. Relationship with Next of Kin: {{ $row->kin_relationship ?? '' }}</td>
    </tr>
</table>

<table class="form-table" style="margin-top:6px;">
    <tr>
        <td colspan="3" class="ul">14. DECLARATION: I _______________________________________ hereby confirm that the</td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top:5px;">
            information given above are correct. &nbsp;&nbsp; Signature: _________________ &nbsp;&nbsp;&nbsp; Date: _________________
        </td>
    </tr>
    <tr><td colspan="3" style="padding-top:10px;"></td></tr>
    <tr>
        <td><strong>HOD</strong> _____________________</td>
        <td>Signature _________________</td>
        <td>Date: _________________</td>
    </tr>
    <tr><td colspan="3" style="padding-top:8px;"></td></tr>
    <tr>
        <td><strong>Director HRS</strong> _____________</td>
        <td>Signature _________________</td>
        <td>Date: _________________</td>
    </tr>
    <tr><td colspan="3" style="padding-top:8px;"></td></tr>
    <tr>
        <td><strong>Head IAU</strong> ________________</td>
        <td>Signature _________________</td>
        <td>Date: _________________</td>
    </tr>
</table>

<div class="note-section">
    <p>NOTE:</p>
    <ol>
        <li>Attach a copy of your June {{ date('Y') }} salary pay slip. Endeavour to return the completed form to the <strong>Internal Audit Unit</strong> not later than <strong>Monday 3rd August {{ date('Y') }}</strong>.</li>
        <li>Uncompleted Forms will be treated as non-submission.</li>
        <li>Failure to return the form as requested above could result to non-payment of salary for the month of <strong>August {{ date('Y') }}</strong> to those concerned.</li>
    </ol>
</div>

<div class="warning">False certification will not be condoned.</div>

</div>

</body>
</html>
