<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Applicant Details' }}</title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.9;
            color: #333;
            margin: 50px 35px 0 35px;
            padding: 0;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a237e;
        }

        .logo {
            width: 80px;
        }

        .header-content {
            flex: 1;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #1a237e;
            line-height: 1.2;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 10px;
        }

        .photo {
            width: 80px;
            height: 100px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #008ed6;
            color: white;
            padding: 3px 8px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px 5px;
        }

        .col-4 {
            width: 33.33%;
            padding: 0 5px;
            box-sizing: border-box;
            margin-bottom: 5px;
        }

        .col-6 {
            width: 50%;
            padding: 0 5px;
            box-sizing: border-box;
            margin-bottom: 5px;
        }

        .field {
            margin-bottom: 5px;
            display: flex;
            flex-wrap: wrap;
        }

        .label {
            font-weight: bold;
            color: #555;
            margin-right: 5px;
            min-width: 100px;
        }

        .value {
            flex: 1;
            word-break: break-word;
        }

        .subject-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            margin-top: 5px;
        }

        .subject-item {
            border: 1px solid #e0e0e0;
            padding: 3px 5px;
            font-size: 12px;
            border-radius: 3px;
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>

<body style="position: relative;">
    <!-- Watermark -->
    <div
        style="
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(0deg);
        opacity: 0.1;
        z-index: -1;
        pointer-events: none;
    ">
        <img src="{{ public_path('uploads/logo.png') }}" alt="Watermark" style="width: 600px; height: auto;">
    </div>
    <!-- Header -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0"
        style="margin-bottom: 20px; table-layout: fixed;">
        <tr>
            <!-- Logo Column -->
            <td width="20%" style="vertical-align: middle; padding-right: 15px;">
                @if (isset($logoData) && !empty($logoData))
                    <img src="{{ $logoData }}" alt="University Logo"
                        style="height: 80px; width: auto; max-width: 100%;">
                @endif
            </td>
            `
            <!-- University Info Column -->
            <td width="60%" style="vertical-align: middle; text-align: center; padding: 0 15px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: bold; line-height: 1.2; color: #008ed6;">UNIVERSITY
                    OF MAIDUGURI
                </h1>
                <p style="margin: 3px 0; font-size: 14px; line-height: 1.2;">ADMISSION OFFICE</p>
                <p style="margin: 3px 0 0 0; font-size: 12px; line-height: 1.2;">P.M.B. 1069, Maiduguri, Borno State,
                    Nigeria</p>
            </td>

            <!-- Photo Column -->
            <td width="20%" style="vertical-align: middle; text-align: right;">
                @php
                    $photoPath = DB::table('document_uploads')
                        ->where(['user_id' => $applicant->user_id, 'doc_type' => 'passport_photo'])
                        ->value('file_path');
                    $photoUrl = $photoPath ? public_path('storage/' . $photoPath) : null;
                @endphp
                @if ($photoUrl && file_exists($photoUrl))
                    <div
                        style="display: inline-block; width: 70px; height: 80px; border: 1px solid #ddd; overflow: hidden; text-align: center;">
                        <img src="{{ $photoUrl }}" alt="Applicant Photo" style="max-height: 100%; max-width: 100%;">
                    </div>
                @else
                    <div
                        style="display: inline-block; width: 70px; height: 80px; border: 1px solid #ddd; background: #f9f9f9; text-align: center; line-height: 80px;">
                        <span style="font-size: 8px; color: #999;">No Photo</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title"
        style="text-align: center; margin-bottom: 15px; margin-top: 15px; font-size: 16px; font-weight: bold;">POST UTME
        SCREENING FORM</div>
    <!-- Personal and Next of Kin Information Side by Side -->
    <div style="width: 100%; margin-bottom: 15px;">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <!-- Personal Information - Left -->
                <td width="48%" valign="top" style="padding-right: 2%;">
                    <div class="section" style="margin-bottom: 0;">
                        <div class="section-title">1. PERSONAL INFORMATION</div>
                        <div class="field">
                            <span class="label">Full Name:</span>
                            <span class="value">{{ $applicant->fullname ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Gender:</span>
                            <span class="value">{{ $applicant->gender ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Date of Birth:</span>
                            <span
                                class="value">{{ $applicant->dob ? \Carbon\Carbon::parse($applicant->dob)->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Email:</span>
                            <span class="value">{{ $applicant->email ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Phone:</span>
                            <span class="value">{{ $applicant->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">State of Origin:</span>
                            <span class="value">{{ $applicant->state ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">LGA:</span>
                            <span class="value">{{ $applicant->lga ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Address:</span>
                            <span class="value">{{ $applicant->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </td>

                <!-- Next of Kin Information - Right -->
                <td width="48%" valign="top" style="padding-left: 2%;">
                    <div class="section" style="margin-bottom: 0;">
                        <div class="section-title">2. NEXT OF KIN INFORMATION</div>
                        <div class="field">
                            <span class="label">Name:</span>
                            <span class="value">{{ $applicant->n_name ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Relationship:</span>
                            <span class="value">{{ $applicant->n_relationship ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Email:</span>
                            <span class="value">{{ $applicant->n_email ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Phone:</span>
                            <span class="value">{{ $applicant->n_phone ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Address:</span>
                            <span class="value">{{ $applicant->n_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    </div>

    <!-- Sponsor and Academic Information Row -->
    <div style="width: 100%; margin: 15px 0;">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <!-- Sponsor Information - Left -->
                <td width="48%" valign="top" style="padding-right: 2%;">
                    <div class="section" style="margin-bottom: 0;">
                        <div class="section-title">3. SPONSOR INFORMATION</div>
                        <div class="field">
                            <span class="label">Name:</span>
                            <span class="value">{{ $applicant->s_name ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Phone:</span>
                            <span class="value">{{ $applicant->s_phone ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Address:</span>
                            <span class="value">{{ $applicant->s_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </td>

                <!-- Academic Information - Right -->
                <td width="48%" valign="top" style="padding-left: 2%;">
                    <div class="section" style="margin-bottom: 0;">
                        <div class="section-title">4. ACADEMIC INFORMATION</div>
                        <div class="field">
                            <span class="label">Faculty:</span>
                            <span class="value">{{ $faculty ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Department:</span>
                            <span class="value">{{ $department ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Program:</span>
                            <span class="value">{{ $program ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Application Type:</span>
                            <span class="value">{{ $applicant->mode ?? 'N/A' }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="width: 100%; margin: 15px 0;">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <!-- JAMB Information - Left -->
                <td width="48%" valign="top" style="padding-right: 2%;">
                    @if (count($jambData) > 0)
                        @php $firstJamb = $jambData->first(); @endphp
                        <div class="section" style="margin-bottom: 0;">
                            <div class="section-title">5. JAMB INFORMATION</div>
                            @if (isset($applicant->username) || isset($applicant->score) || isset($applicant->session))
                                <div class="row">
                                    @if (isset($applicant->username))
                                        <div class="col-6">
                                            <div class="field">
                                                <span class="label">JAMB No:</span>
                                                <span class="value">{{ $applicant->username }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($applicant->score))
                                        <div class="col-6">
                                            <div class="field">
                                                <span class="label">JAMB Score:</span>
                                                <span class="value">{{ $applicant->score }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($applicant->session))
                                        <div class="col-6">
                                            <div class="field">
                                                <span class="label">Exam Year:</span>
                                                <span class="value">{{ $applicant->session }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($jambData->where('subject', '!=', null)->count() > 0)
                                <div style="margin-top: 5px;">
                                    <div style="font-weight: bold; margin: 5px 0; font-size: 10px;">JAMB SUBJECTS &
                                        SCORES</div>
                                    <div class="subject-grid">
                                        @foreach ($jambData as $jamb)
                                            @if (isset($jamb->subject))
                                                <div class="subject-item">
                                                    <strong>{{ $jamb->subject }}:</strong>
                                                    {{ $jamb->score ?? 'N/A' }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif(strtoupper($applicant->mode) === 'DE' && isset($deData) && $deData)
                        <div class="section" style="margin-bottom: 0;">
                            <div class="section-title">5. DIRECT ENTRY QUALIFICATION</div>
                            <div class="row">

                                <div class="field">
                                    <span class="label">JAMB No:</span>
                                    <span class="value">{{ $applicant->username ?? 'N/A' }}</span>
                                </div>

                                <div class="field">
                                    <span class="label">Qualification:</span>
                                    <span class="value">{{ $deData->qualification_type ?? 'N/A' }}</span>
                                </div>
                                <div class="field">
                                    <span class="label">Institution:</span>
                                    <span class="value">{{ $deData->institution ?? 'N/A' }}</span>
                                </div>
                                <div class="field">
                                    <span class="label">Year:</span>
                                    <span class="value">{{ $deData->grad_year ?? 'N/A' }}</span>
                                </div>
                                <div class="field">
                                    <span class="label">Grade:</span>
                                    <span class="value">{{ $deData->grade ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </td>

                <!-- SSCE Results - Right -->
                <td width="48%" valign="top" style="padding-left: 2%;">
                    @php
                        $ssceData = $ssceData ?? collect();
                        $ssceResults = $ssceResults ?? [];
                    @endphp

                    @if ($ssceData->count() > 0)
                        <div class="section" style="margin-bottom: 0;">
                            <div class="section-title">6. SSCE RESULTS</div>
                            @foreach ($ssceData as $index => $ssce)
                                @if ($ssce && isset($ssce->id) && isset($ssceResults[$ssce->id]) && count($ssceResults[$ssce->id]) > 0)
                                    <div style="margin-bottom: 10px;">
                                        <div
                                            style="font-weight: bold; font-size: 9px; margin-bottom: 3px; color: #1a237e;">
                                            {{ $ssce->type ?? 'SSCE' }} ({{ $ssce->year ?? 'N/A' }})
                                        </div>
                                        @php
                                            $results = $ssceResults[$ssce->id]->filter()->values();
                                            $half = ceil($results->count() / 2);
                                            $firstColumn = $results->take($half);
                                            $secondColumn = $results->slice($half);
                                        @endphp
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="48%" valign="top" style="padding-right: 2%;">
                                                    @foreach ($firstColumn as $result)
                                                        <div class="subject-item" style="margin-bottom: 2px;">
                                                            {{ $result->subject ?? 'N/A' }}:
                                                            <strong>{{ $result->grade ?? 'N/A' }}</strong>
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td width="48%" valign="top" style="padding-left: 2%;">
                                                    @foreach ($secondColumn as $result)
                                                        <div class="subject-item" style="margin-bottom: 2px;">
                                                            {{ $result->subject ?? 'N/A' }}:
                                                            <strong>{{ $result->grade ?? 'N/A' }}</strong>
                                                        </div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    </div>
</body>

</html>
