<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Letter - {{ $applicant->fullname }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.75in;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .university-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            background: #fff;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .university-name {
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .office-title {
            font-size: 14pt;
            font-weight: normal;
            margin: 3px 0;
        }
        
        .motto {
            font-style: italic;
            font-size: 10pt;
            margin: 5px 0;
            color: #444;
        }
        
        .address {
            font-size: 10pt;
            margin: 5px 0;
            color: #444;
        }
        
        .letter-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .reference-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .ref-left, .ref-right {
            width: 48%;
        }
        
        .ref-item {
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .ref-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .content {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 12pt;
        }
        
        .content p {
            margin-bottom: 12px;
            text-indent: 0;
        }
        
        .salutation {
            margin-bottom: 15px;
            font-size: 12pt;
        }
        
        .program-details {
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }
        
        .program-details table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        
        .program-details td {
            padding: 5px 8px;
            border-bottom: 1px solid #ccc;
        }
        
        .program-details .label {
            font-weight: bold;
            width: 30%;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .signature-box {
            text-align: center;
            width: 180px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
            margin-top: 30px;
        }
        
        .signature-title {
            font-size: 11pt;
            font-weight: bold;
        }
        
        .signature-subtitle {
            font-size: 10pt;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
            color: #444;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        .no-print {
            display: block;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button class="btn btn-primary print-button no-print" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Letter
    </button>
    
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="header-section">
            <div class="university-logo">UNIMAID</div>
            <div class="university-name">{{ $letterContent['letterhead'] }}</div>
            <div class="office-title">OFFICE OF THE REGISTRAR</div>
            <div class="motto">"Knowledge for Service"</div>
            <div class="address">PMB 1069, Maiduguri, Borno State, Nigeria</div>
        </div>

        <!-- Reference Section -->
        <div class="reference-section">
            <div class="ref-left">
                <div class="ref-item">
                    <span class="ref-label">Our Ref:</span>
                    UNIMAID/REG/ADM/{{ date('Y') }}/{{ str_pad(substr($applicant->id, -6), 6, '0', STR_PAD_LEFT) }}
                </div>
                <div class="ref-item">
                    <span class="ref-label">Your Ref:</span>
                    {{ $letterContent['admission_number'] }}
                </div>
            </div>
            <div class="ref-right">
                <div class="ref-item" style="text-align: right;">
                    <span class="ref-label">Date:</span>
                    {{ $letterContent['date'] }}
                </div>
            </div>
        </div>

        <!-- Letter Title -->
        <div class="letter-title">{{ $letterContent['title'] }}</div>

        <!-- Applicant Address -->
        <div class="salutation">
            <strong>{{ $applicant->fullname }}</strong><br>
            {{ $applicant->address ?: 'Address on file' }}
        </div>

        <div class="salutation">
            <strong>Dear {{ $applicant->first_name }},</strong>
        </div>

        <div class="letter-title" style="font-size: 14pt; margin: 15px 0;">
            <strong>OFFER OF PROVISIONAL ADMISSION</strong>
        </div>

        <!-- Main Content -->
        <div class="content">
            <p>I am pleased to inform you that following the consideration of your application for admission into the University of Maiduguri, you have been offered <strong>PROVISIONAL ADMISSION</strong> to study in the {{ $letterContent['session'] }} Academic Session.</p>

            <p>The details of your admission are as follows:</p>
            
            <!-- Program Details -->
            <div class="program-details">
                <table>
                    <tr>
                        <td class="label">Faculty:</td>
                        <td>{{ $letterContent['faculty']->title ?? $applicant->faculty }}</td>
                    </tr>
                    <tr>
                        <td class="label">Department:</td>
                        <td>{{ $letterContent['department']->title ?? $applicant->department }}</td>
                    </tr>
                    <tr>
                        <td class="label">Programme:</td>
                        <td>{{ $letterContent['program']->title ?? $applicant->program }}</td>
                    </tr>
                    <tr>
                        <td class="label">Mode of Entry:</td>
                        <td>{{ $applicant->mode == 'DE' ? 'Direct Entry' : 'UTME' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Academic Session:</td>
                        <td>{{ $letterContent['session'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Admission Number:</td>
                        <td>{{ $letterContent['admission_number'] }}</td>
                    </tr>
                </table>
            </div>

            <p><strong>IMPORTANT INFORMATION:</strong></p>
            
            <p>This admission is <strong>PROVISIONAL</strong> and will be confirmed upon:</p>
            <ol>
                <li>Payment of acceptance fee and other prescribed fees</li>
                <li>Verification of your O'Level results and other credentials</li>
                <li>Satisfactory completion of medical examination</li>
                <li>Compliance with all admission requirements</li>
            </ol>

            <p><strong>NEXT STEPS:</strong></p>
            <ol>
                <li><strong>Accept Your Offer:</strong> Log in to your admission portal to formally accept this offer</li>
                <li><strong>Pay Fees:</strong> Pay the prescribed acceptance fee and other charges as directed</li>
                <li><strong>Registration:</strong> Complete your course registration during the specified period</li>
                <li><strong>Orientation:</strong> Attend the mandatory orientation programme for new students</li>
            </ol>

            <p style="color: #e53e3e; font-weight: bold;">
                Note: Failure to accept this offer and complete registration within the stipulated time will result in forfeiture of your admission.
            </p>

            <p>We congratulate you on this achievement and look forward to welcoming you to the University of Maiduguri community. We are confident that you will make meaningful contributions to academic and social life on our campus.</p>

            <p>Once again, congratulations and welcome to UNIMAID!</p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div></div> <!-- Empty space -->
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Prof. Mohammed Liman</div>
                <div class="signature-subtitle">Registrar</div>
                <div class="signature-subtitle">For: Vice-Chancellor</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>University of Maiduguri - "Knowledge for Service"</strong></p>
            <p>This is an official document of the University of Maiduguri | Generated on {{ date('F j, Y g:i A') }}</p>
            <p style="color: #e53e3e; font-size: 11px;">
                <strong>IMPORTANT:</strong> This admission letter is valid only if all requirements are met. 
                Any discrepancy should be reported to the Admissions Office immediately.
            </p>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <!-- Auto-print functionality -->
    <script>
        // Auto-focus for printing
        window.addEventListener('load', function() {
            // Optional: Auto-print when page loads
            // window.print();
        });
        
        // Print function
        function printLetter() {
            window.print();
        }
    </script>
</body>
</html>
