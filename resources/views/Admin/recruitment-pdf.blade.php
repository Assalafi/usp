<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Applicants Export Report</title>
    <style>
        @page {
            margin: 1cm;
            orientation: landscape;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #333;
        }
        
        .header p {
            font-size: 10px;
            margin: 5px 0;
            color: #666;
        }
        
        .filters {
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .filters strong {
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
            white-space: nowrap;
        }
        
        td {
            font-size: 9px;
        }
        
        .sno {
            width: 40px;
            text-align: center;
        }
        
        .name {
            width: 150px;
        }
        
        .gender {
            width: 50px;
            text-align: center;
        }
        
        .date-of-birth {
            width: 70px;
            text-align: center;
        }
        
        .state {
            width: 80px;
        }
        
        .lga {
            width: 80px;
        }
        
        .qualification {
            width: 120px;
        }
        
        .post-applied {
            width: 130px;
        }
        
        .department {
            width: 100px;
        }
        
        .gsm-no {
            width: 90px;
        }
        
        .status {
            width: 60px;
            text-align: center;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .no-data {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin: 50px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UNIVERSITY OF MAIDUGURI</h1>
        <p>RECRUITMENT APPLICANTS REPORT</p>
        <p>Generated on: {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(!empty($filters['department'])) Department: {{ $filters['department'] }} @endif
        @if(!empty($filters['post_applied'])) | Post Applied: {{ $filters['post_applied'] }} @endif
        @if(!empty($filters['state'])) | State: {{ $filters['state'] }} @endif
        @if(!empty($filters['lga'])) | LGA: {{ $filters['lga'] }} @endif
        @if(!empty($filters['gender'])) | Gender: {{ $filters['gender'] }} @endif
        @if(!empty($filters['status'])) | Status: {{ $filters['status'] }} @endif
    </div>
    @endif
    
    @if(!empty($applicants))
    <table>
        <thead>
            <tr>
                <th class="sno">S/NO</th>
                <th class="name">NAME</th>
                <th class="gender">GENDER</th>
                <th class="date-of-birth">DATE OF BIRTH</th>
                <th class="state">STATE</th>
                <th class="lga">LGA</th>
                <th class="qualification">QUALIFICATION</th>
                <th class="post-applied">POST APPLIED</th>
                <th class="department">DEPARTMENT</th>
                <th class="gsm-no">GSM NO</th>
                <th class="status">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applicants as $applicant)
            <tr>
                <td class="sno">{{ $applicant['sno'] }}</td>
                <td class="name">{{ $applicant['name'] }}</td>
                <td class="gender">{{ $applicant['gender'] }}</td>
                <td class="date-of-birth">{{ $applicant['date_of_birth'] }}</td>
                <td class="state">{{ $applicant['state'] }}</td>
                <td class="lga">{{ $applicant['lga'] }}</td>
                <td class="qualification">{{ $applicant['qualification'] }}</td>
                <td class="post-applied">{{ $applicant['post_applied'] }}</td>
                <td class="department">{{ $applicant['department'] }}</td>
                <td class="gsm-no">{{ $applicant['gsm_no'] }}</td>
                <td class="status">{{ $applicant['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Total Applicants: {{ count($applicants) }}</p>
        <p>This report contains applications based on selected status filter</p>
        <p>© University of Maiduguri - Recruitment System</p>
    </div>
    @else
    <div class="no-data">
        <p>No applicants found matching the selected criteria.</p>
    </div>
    @endif
</body>
</html>
