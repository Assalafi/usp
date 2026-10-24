@php
    $rows = collect($rows ?? []);
    $logo = public_path('uploads/logo.png');
    $categoryLabels = [
        'General' => 'GENERAL ELECTION',
        'Faculty Rep' => 'FACULTY REPRESENTATIVES',
        'Hostel Rep' => 'HOSTEL REPRESENTATIVES',
        'Lga Rep' => 'LGA REPRESENTATIVES',
    ];
    $generalOfficeLabels = [
        'PRESIDENT' => 'Presidential Aspirant',
        'VICE PRESIDENT' => 'Vice-president',
        'SECRETARY GENERAL' => 'Secretary General Aspirant',
        'FINANCIAL SECRETRY' => 'Financial Secretary Aspirant',
        'FINANCIAL SECRETARY' => 'Financial Secretary Aspirant',
        'ORGANIZING SECRETARY' => 'Organizing Secretary Aspirant',
        'AUDITOR GENERAL' => 'Auditor General Aspirant',
        'ACADEMICS DIRECTOR' => 'Academics Director Aspirant',
        'SPORT DIRECTOR' => 'Sport Director Aspirant',
        'PUBLIC RELATION OFFICER' => 'Public Relations Officer Aspirant',
        'TREASURER' => 'Treasurer Aspirant',
        'STUDENT REP ASSEMBLY' => 'Student Representative Assembly Aspirant',
    ];
    $categoryOrder = ['General', 'Faculty Rep', 'Hostel Rep', 'Lga Rep'];
    $orderedRows = $rows->sortBy(function ($row) use ($categoryOrder) {
        $category = array_search((string) ($row->category ?? ''), $categoryOrder, true);
        return sprintf('%02d-%05d-%s', $category === false ? 99 : $category, (int) ($row->order ?? 99999), strtolower((string) ($row->position ?? '')));
    })->values();
    $sessionText = trim((string) ($session ?? '')) ?: '________________';
    $eventDateText = trim((string) ($electionDate ?? '')) ?: '________________';
    $venueText = strtoupper(trim((string) ($venue ?? 'ALIYU SHUGABA CONFERENCES HALL')));
    $hasGeneral = $orderedRows->where('category', 'General')->isNotEmpty();
    $hasRepresentatives = $orderedRows->whereIn('category', ['Faculty Rep', 'Hostel Rep', 'Lga Rep'])->isNotEmpty();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 15mm 17mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #171717; font-family: DejaVu Serif, "Times New Roman", serif; font-size: 9.4px; line-height: 1.22; }
        .watermark { position: fixed; top: 85mm; left: 63mm; width: 85mm; opacity: .035; }
        .official-heading { text-align: center; margin-bottom: 13px; }
        .official-heading .university { font-size: 14px; font-weight: bold; letter-spacing: .25px; }
        .official-heading .office { margin-top: 3px; font-size: 9.5px; font-style: italic; }
        .official-heading .division { margin-top: 4px; font-size: 10.5px; font-weight: bold; }
        .official-heading .report-title { max-width: 178mm; margin: 10px auto 0; font-size: 10px; font-weight: bold; text-transform: uppercase; line-height: 1.3; }
        .official-heading .held { max-width: 178mm; margin: 2px auto 0; font-size: 9.3px; text-transform: uppercase; line-height: 1.3; }
        .rule { height: 1px; margin: 8px 0 12px; background: #1a1a1a; }
        .category-heading { margin: 15px 0 8px; text-align: center; font-size: 10.5px; font-weight: bold; text-transform: uppercase; }
        .office-heading { margin: 13px 0 5px; font-size: 10.5px; font-weight: bold; }
        .office-heading:first-child { margin-top: 4px; }
        .entity-heading { margin: 11px 0 4px; font-size: 9.7px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #777; padding: 4px 5px; vertical-align: top; }
        th { font-size: 8.5px; font-weight: normal; text-align: left; line-height: 1.15; }
        td { min-height: 17px; font-size: 8.8px; }
        .center { text-align: center; }
        .general-table th:nth-child(1) { width: 8%; }
        .general-table th:nth-child(2) { width: 37%; }
        .general-table th:nth-child(3) { width: 20%; }
        .general-table th:nth-child(4) { width: 17%; }
        .general-table th:nth-child(5) { width: 18%; }
        .representative-table th:nth-child(1) { width: 7%; }
        .representative-table th:nth-child(2) { width: 16%; }
        .representative-table th:nth-child(3) { width: 23%; }
        .representative-table th:nth-child(4) { width: 9%; }
        .representative-table th:nth-child(5) { width: 10%; }
        .representative-table th:nth-child(6) { width: 13%; }
        .representative-table th:nth-child(7) { width: 11%; }
        .representative-table th:nth-child(8) { width: 11%; }
        .candidate-name { font-weight: normal; }
        .candidate-office { display: block; margin-top: 2px; font-size: 7.6px; color: #555; }
        .remark { font-weight: bold; }
        .remark.elected { color: #111; }
        .remark.not-elected { color: #555; }
        .empty-state { margin: 30px 0; text-align: center; font-style: italic; }
        .footer { position: fixed; right: 0; bottom: -10mm; left: 0; text-align: center; font-size: 7px; color: #555; }
        .page-number:after { content: counter(page); }
        .small-note { margin-top: 8px; font-size: 7.5px; font-style: italic; }
    </style>
</head>
<body>
    @if (is_file($logo))
        <img class="watermark" src="{{ $logo }}" alt="">
    @endif

    <div class="official-heading">
        <div class="university">UNIVERSITY OF MAIDUGURI</div>
        <div class="office">(OFFICE OF THE VICE CHANCELLOR)</div>
        <div class="division">STUDENT AFFAIRS DIVISION</div>
        @if ($hasGeneral && !$hasRepresentatives)
            <div class="report-title">RESULT OF ELECTION OF SUG EXECUTIVES FOR {{ $sessionText }} ACADEMIC SESSION</div>
        @elseif ($hasRepresentatives && !$hasGeneral)
            <div class="report-title">RESULTS OF ELECTION SUG STUDENT REPRESENTATIVES ASSEMBLY FOR {{ $sessionText }} ACADEMIC SESSION</div>
        @else
            <div class="report-title">RESULTS OF ELECTION OF SUG EXECUTIVES AND STUDENT REPRESENTATIVES FOR {{ $sessionText }} ACADEMIC SESSION</div>
        @endif
        <div class="held">HELD ON {{ $eventDateText }} IN {{ $venueText }} UNIVERSITY OF MAIDUGURI.</div>
    </div>
    <div class="rule"></div>

    @if ($hasGeneral)
        <div class="category-heading">RESULT OF SUG EXECUTIVES</div>
        @php $generalGroups = $orderedRows->where('category', 'General')->groupBy('position'); @endphp
        @foreach ($generalGroups as $position => $group)
            @php
                $positionKey = strtoupper(trim((string) $position));
                $officeLabel = $generalOfficeLabels[$positionKey] ?? ucwords(strtolower((string) $position)) . ' Aspirant';
            @endphp
            <div class="office-heading">{{ $officeLabel }}</div>
            <table class="general-table">
                <thead>
                    <tr>
                        <th class="center">S/No.</th>
                        <th>Number of contestant</th>
                        <th>Total number of<br>votes scored</th>
                        <th>Remarks</th>
                        <th>Agent remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group as $index => $row)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td class="candidate-name">{{ $row->name ?: $row->candidate ?: 'Name not recorded' }}</td>
                            <td class="center">{{ number_format((int) ($row->vote ?? 0)) }}</td>
                            <td class="remark {{ $row->is_elected ? 'elected' : 'not-elected' }}">{{ $row->remark }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    @foreach (['Faculty Rep' => 'FACULTY REPRESENTATIVES', 'Hostel Rep' => 'HOSTEL REPRESENTATIVES', 'Lga Rep' => 'LGA REPRESENTATIVES'] as $representativeCategory => $representativeTitle)
        @php
            $representativeRows = $orderedRows->where('category', $representativeCategory);
            $entityGroups = $representativeRows->groupBy(function ($row) use ($representativeCategory) {
                $value = match ($representativeCategory) {
                    'Faculty Rep' => ($row->entity_label ?: $row->faculty ?: ''),
                    'Hostel Rep' => ($row->entity_label ?: $row->hostel ?: ''),
                    default => ($row->entity_label ?: $row->lga ?: ''),
                };
                return strtolower(trim((string) $value));
            });
        @endphp
        @if ($representativeRows->isNotEmpty())
            <div class="category-heading">{{ $representativeTitle }}</div>
            @foreach ($entityGroups as $entityKey => $group)
                @php
                    $first = $group->first();
                    $entityName = match ($representativeCategory) {
                        'Faculty Rep' => ($first->entity_label ?: $first->faculty ?: 'Entity not recorded'),
                        'Hostel Rep' => ($first->entity_label ?: $first->hostel ?: 'Entity not recorded'),
                        default => ($first->entity_label ?: $first->lga ?: 'Entity not recorded'),
                    };
                @endphp
                <div class="entity-heading">{{ $entityName }}</div>
                <table class="representative-table">
                    <thead>
                        <tr>
                            <th class="center">S/N</th>
                            <th>ID No.</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Score</th>
                            <th>Elect comm.<br>remarks</th>
                            <th>Agent<br>remarks</th>
                            <th>Chairman's<br>remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group as $index => $row)
                            <tr>
                                <td class="center">{{ $index === 0 ? $loop->parent->iteration : '' }}</td>
                                <td>{{ $row->candidate }}</td>
                                <td class="candidate-name">{{ $row->name ?: 'Name not recorded' }}@if($row->position)<span class="candidate-office">{{ $row->position }}</span>@endif</td>
                                <td class="center">{{ $row->level ?: '' }}</td>
                                <td class="center">{{ number_format((int) ($row->vote ?? 0)) }}</td>
                                <td class="remark {{ $row->is_elected ? 'elected' : 'not-elected' }}">{{ $row->remark }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach

    @if ($orderedRows->isEmpty())
        <div class="empty-state">No election records matched the selected filters.</div>
    @endif

    <div class="small-note">Official election result register - University of Maiduguri.</div>
    <div class="footer">Page <span class="page-number"></span></div>
</body>
</html>
