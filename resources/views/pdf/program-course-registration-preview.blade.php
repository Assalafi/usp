<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Curriculum preview</title>
    <style>
        @page { margin: 28px 28px 32px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 8.5px; }
        .masthead { border-bottom: 3px solid #2563eb; padding-bottom: 11px; margin-bottom: 14px; }
        .brand { width: 100%; border-collapse: collapse; }
        .brand td { vertical-align: middle; }
        .brand-logo { width: 48px; height: 48px; object-fit: contain; }
        .brand-title { padding-left: 10px; }
        .brand-title h1 { margin: 0; color: #0f3b82; font-size: 16px; letter-spacing: .2px; }
        .brand-title p { margin: 4px 0 0; color: #64748b; font-size: 8px; }
        .document-title { text-align: right; color: #2563eb; font-size: 12px; font-weight: bold; }
        .document-subtitle { margin-top: 3px; color: #64748b; font-size: 8px; }
        .filters { width: 100%; border-collapse: collapse; margin: 0 0 15px; background: #f8fafc; border: 1px solid #dbe5f0; }
        .filters td { width: 25%; padding: 6px 8px; border-right: 1px solid #e5eaf2; border-bottom: 1px solid #e5eaf2; vertical-align: top; }
        .filters td:nth-child(4n) { border-right: 0; }
        .filters tr:last-child td { border-bottom: 0; }
        .label { display: block; color: #64748b; text-transform: uppercase; font-size: 6.7px; letter-spacing: .4px; font-weight: bold; }
        .value { display: block; margin-top: 2px; color: #172033; font-size: 8px; font-weight: bold; }
        .summary { margin: 0 0 10px; color: #475569; font-size: 8px; }
        .summary strong { color: #0f3b82; }
        table.records { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .records th { padding: 7px 5px; color: #fff; background: #0f3b82; text-align: left; font-size: 7px; text-transform: uppercase; letter-spacing: .3px; }
        .records td { padding: 6px 5px; border: 1px solid #dbe5f0; vertical-align: top; color: #334155; line-height: 1.25; word-wrap: break-word; }
        .records tr:nth-child(even) td { background: #f8fafc; }
        .records .sn { width: 3.5%; text-align: center; }
        .records .faculty { width: 13%; }
        .records .department { width: 13%; }
        .records .program { width: 14%; }
        .records .structure { width: 9%; }
        .records .course { width: 17%; }
        .records .level { width: 6%; text-align: center; }
        .records .semester { width: 7%; }
        .records .unit { width: 5%; text-align: center; }
        .records .type { width: 6%; }
        .records .direct { width: 10%; }
        .records .prereq { width: 10%; }
        .muted { color: #64748b; font-size: 7px; }
        .code { color: #1d4ed8; font-weight: bold; }
        .empty { padding: 25px; border: 1px dashed #cbd5e1; color: #64748b; text-align: center; }
        .footer { position: fixed; bottom: -17px; left: 0; right: 0; border-top: 1px solid #dbe5f0; padding-top: 5px; color: #94a3b8; font-size: 7px; }
        .footer .right { float: right; }
    </style>
</head>
<body>
    <div class="masthead">
        <table class="brand">
            <tr>
                <td style="width:58px;">
                    @if($logoData)
                        <img class="brand-logo" src="{{ $logoData }}" alt="University logo">
                    @endif
                </td>
                <td class="brand-title">
                    <h1>UNIVERSITY OF MAIDUGURI</h1>
                    <p>College curriculum management · Programme course registration</p>
                </td>
                <td style="width:220px; text-align:right;">
                    <div class="document-title">CURRICULUM PREVIEW</div>
                    <div class="document-subtitle">Generated {{ now()->format('d M Y, H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="filters">
        @php($filterEntries = array_values($filters ?? []))
        @for($i = 0; $i < count($filterEntries); $i += 4)
            <tr>
                @for($j = 0; $j < 4; $j++)
                    @if(isset($filterEntries[$i + $j]))
                        @php($filterKey = array_keys($filters)[$i + $j])
                        <td><span class="label">{{ $filterKey }}</span><span class="value">{{ $filterEntries[$i + $j] }}</span></td>
                    @else
                        <td></td>
                    @endif
                @endfor
            </tr>
        @endfor
    </table>

    <div class="summary">Showing <strong>{{ number_format(count($rows ?? [])) }}</strong> curriculum mapping(s). Course titles and units come from the master course catalogue.</div>

    @if(count($rows ?? []))
        <table class="records">
            <thead>
                <tr>
                    <th class="sn">SN</th>
                    <th class="faculty">Faculty / college</th>
                    <th class="department">Department</th>
                    <th class="program">Programme</th>
                    <th class="structure">Structure</th>
                    <th class="course">Course</th>
                    <th class="level">Level</th>
                    <th class="semester">Semester</th>
                    <th class="unit">Units</th>
                    <th class="type">Type</th>
                    <th class="direct">Direct entry</th>
                    <th class="prereq">Prerequisite</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td class="sn">{{ $index + 1 }}</td>
                        <td>{{ $row->faculty_title ?: ($row->faculty ?? '—') }}</td>
                        <td>{{ $row->department_title ?: ($row->department ?? '—') }}</td>
                        <td>{{ $row->program_title ?: $row->program }}<br><span class="muted">{{ $row->program }}</span></td>
                        <td>{{ $row->structure_name ?: ('#' . $row->structure_id) }}<br><span class="muted">{{ $row->structure_from_session }}–{{ $row->structure_to_session }}</span></td>
                        <td><span class="code">{{ $row->code }}</span><br>{{ $row->course_title ?: 'Course title not found' }}</td>
                        <td class="level">{{ $row->level }}</td>
                        <td>{{ $row->semester }}</td>
                        <td class="unit">{{ $row->unit ?? 0 }}</td>
                        <td>{{ strtoupper($row->type ?: 'CORE') }}</td>
                        <td>{{ (int) ($row->de ?? 0) === 1 ? 'Foundation course' : 'No' }}</td>
                        <td>{{ $row->prerequisites ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No curriculum mappings matched the selected filters.</div>
    @endif

    <div class="footer">Official curriculum preview · {{ config('app.name', 'University of Maiduguri') }}<span class="right">Page <span class="pagenum"></span></span></div>
</body>
</html>
