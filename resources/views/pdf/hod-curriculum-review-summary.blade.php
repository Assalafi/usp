<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>HOD curriculum review summary</title>
    <style>
        @page { margin: 28px 30px 32px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .masthead { border-bottom: 3px solid #2563eb; padding-bottom: 11px; margin-bottom: 15px; }
        .brand { width: 100%; border-collapse: collapse; }
        .brand td { vertical-align: middle; }
        .logo { width: 48px; height: 48px; object-fit: contain; }
        .title { padding-left: 10px; }
        .title h1 { margin: 0; color: #0f3b82; font-size: 16px; letter-spacing: .2px; }
        .title p { margin: 4px 0 0; color: #64748b; font-size: 8px; }
        .document { text-align: right; color: #2563eb; font-size: 12px; font-weight: bold; }
        .document small { display: block; margin-top: 4px; color: #64748b; font-size: 8px; font-weight: normal; }
        .context { width: 100%; border-collapse: collapse; margin-bottom: 14px; background: #f8fafc; border: 1px solid #dbe5f0; }
        .context td { padding: 8px 10px; border-right: 1px solid #e5eaf2; vertical-align: top; }
        .context td:last-child { border-right: 0; }
        .label { display: block; color: #64748b; text-transform: uppercase; font-size: 7px; letter-spacing: .4px; font-weight: bold; }
        .value { display: block; margin-top: 3px; color: #172033; font-size: 9px; font-weight: bold; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 14px; }
        .summary td { width: 33.33%; padding: 10px 12px; border: 1px solid #dbe5f0; background: #fff; }
        .summary .number { display: block; color: #0f3b82; font-size: 17px; font-weight: bold; }
        .summary .caption { color: #64748b; font-size: 8px; }
        table.records { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .records th { padding: 7px 6px; color: #fff; background: #0f3b82; text-align: left; font-size: 7px; text-transform: uppercase; letter-spacing: .3px; }
        .records td { padding: 7px 6px; border: 1px solid #dbe5f0; vertical-align: top; color: #334155; line-height: 1.25; word-wrap: break-word; }
        .records tr:nth-child(even) td { background: #f8fafc; }
        .sn { width: 4%; text-align: center; }.hod { width: 22%; }.department { width: 22%; }.status { width: 14%; }.submitted { width: 16%; }.changed { width: 22%; }
        .status-submitted { color: #166534; font-weight: bold; }.status-pending { color: #9a6700; font-weight: bold; }
        .muted { color: #64748b; font-size: 7px; }
        .empty { padding: 25px; border: 1px dashed #cbd5e1; color: #64748b; text-align: center; }
        .footer { position: fixed; bottom: -17px; left: 0; right: 0; border-top: 1px solid #dbe5f0; padding-top: 5px; color: #94a3b8; font-size: 7px; }
        .footer .right { float: right; }
    </style>
</head>
<body>
    <div class="masthead">
        <table class="brand"><tr>
            <td style="width:58px;">@if($logoData)<img class="logo" src="{{ $logoData }}" alt="University logo">@endif</td>
            <td class="title"><h1>UNIVERSITY OF MAIDUGURI</h1><p>Academic curriculum management · Head of Department review monitoring</p></td>
            <td style="width:250px;"><div class="document">HOD CURRICULUM REVIEW SUMMARY<small>Generated {{ now()->format('d M Y, H:i') }}</small></div></td>
        </tr></table>
    </div>

    <table class="context"><tr>
        <td><span class="label">Curriculum structure</span><span class="value">{{ $structure->name ?? '—' }}</span></td>
        <td><span class="label">Academic period</span><span class="value">{{ $structure->from_session ?? '—' }}–{{ $structure->to_session ?? '—' }}</span></td>
        <td><span class="label">Report scope</span><span class="value">Active Heads of Department</span></td>
    </tr></table>

    <table class="summary"><tr>
        <td><span class="number">{{ number_format($submittedCount) }}</span><span class="caption">HODs who submitted confirmation</span></td>
        <td><span class="number">{{ number_format($pendingCount) }}</span><span class="caption">HODs still pending review</span></td>
        <td><span class="number">{{ number_format($reviews->count()) }}</span><span class="caption">Active HODs in this report</span></td>
    </tr></table>

    @if($reviews->count())
        <table class="records">
            <thead><tr><th class="sn">SN</th><th class="hod">Head of Department</th><th class="department">Department</th><th class="status">Review status</th><th class="submitted">Submitted</th><th class="changed">Latest curriculum change</th></tr></thead>
            <tbody>
            @foreach($reviews as $index => $review)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td class="hod"><strong>{{ $review->name ?: 'Unnamed HOD' }}</strong><br><span class="muted">{{ $review->username }}</span></td>
                    <td class="department">{{ $review->department_title ?: $review->department }}</td>
                    <td class="status {{ $review->review_status === 'submitted' ? 'status-submitted' : 'status-pending' }}">{{ $review->review_status === 'submitted' ? 'Submitted' : 'Pending review' }}</td>
                    <td class="submitted">{{ $review->submitted_at ? \Illuminate\Support\Carbon::parse($review->submitted_at)->format('j M Y, g:i a') : '—' }}</td>
                    <td class="changed">{{ $review->last_changed_at ? \Illuminate\Support\Carbon::parse($review->last_changed_at)->format('j M Y, g:i a') : 'No change recorded' }}@if($review->last_change_action)<br><span class="muted">{{ $review->last_change_action }}</span>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No active Heads of Department were found for this report.</div>
    @endif

    <div class="footer">Official academic monitoring report · {{ config('app.name', 'University of Maiduguri') }}<span class="right">Page <span class="pagenum"></span></span></div>
</body>
</html>
