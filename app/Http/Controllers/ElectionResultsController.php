<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ElectionResultsExport;

class ElectionResultsController extends Controller
{
    private const CATEGORIES = ['General', 'Faculty Rep', 'Hostel Rep', 'Lga Rep'];

    public function index(Request $request)
    {
        $this->guard();

        $allRows = $this->decorate($this->candidateQuery()->get());
        $rows = $this->filterRows($allRows, $request);
        $category = $this->normaliseCategory($request->input('category'));
        $offices = $allRows
            ->when($category, fn (Collection $items) => $items->where('category', $category))
            ->pluck('position')->filter()->unique()->sort()->values();
        $entityOptions = $this->entityOptions($allRows, $category);
        $summary = [
            'candidates' => $rows->count(),
            'offices' => $rows->map(fn ($row) => strtolower($row->category . '|' . $row->position))->unique()->count(),
            'votes' => (int) $rows->sum(fn ($row) => (int) ($row->vote ?? 0)),
            'elected' => $rows->where('is_elected', true)->count(),
            'voters' => DB::table('election_votes')->when($category, fn ($query) => $query->where('category', $category))->distinct('username')->count('username'),
        ];
        $categorySummary = collect(self::CATEGORIES)->mapWithKeys(function ($item) use ($allRows) {
            $items = $allRows->where('category', $item);
            return [$item => [
                'candidates' => $items->count(),
                'offices' => $items->map(fn ($row) => strtolower((string) $row->position))->unique()->count(),
                'votes' => (int) $items->sum(fn ($row) => (int) ($row->vote ?? 0)),
                'elected' => $items->where('is_elected', true)->count(),
            ]];
        });

        return view('main', [
            'page' => 'election results',
            'title' => 'ELECTION RESULTS',
            'electionRows' => $rows,
            'electionSummary' => $summary,
            'electionCategorySummary' => $categorySummary,
            'electionCategories' => self::CATEGORIES,
            'electionCategory' => $category ?: 'all',
            'electionOffices' => $offices,
            'electionEntities' => $entityOptions,
            'electionFilters' => [
                'office' => (string) $request->input('office', 'all'),
                'entity' => (string) $request->input('entity', 'all'),
                'status' => (string) $request->input('status', 'all'),
                'search' => (string) $request->input('search', ''),
            ],
            'electionSession' => DB::table('election_settings')->where('description', 'Session')->value('value')
                ?: DB::table('session')->where('status', '1')->value('title'),
            'electionDate' => DB::table('election_settings')->where('description', 'Election Date')->value('value')
                ?: now()->format('jS F, Y'),
        ]);
    }

    public function export(Request $request)
    {
        $this->guard();
        $rows = $this->filterRows($this->decorate($this->candidateQuery()->get()), $request);
        $name = 'election-results-' . strtolower(str_replace(' ', '-', (string) ($request->input('category') ?: 'all'))) . '-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new ElectionResultsExport($rows), $name);
    }

    public function pdf(Request $request)
    {
        $this->guard();
        $rows = $this->filterRows($this->decorate($this->candidateQuery()->get()), $request);
        $category = $this->normaliseCategory($request->input('category'));
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', public_path());
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.election-results-list', [
            'rows' => $rows,
            'category' => $category ?: 'All election categories',
            'session' => DB::table('election_settings')->where('description', 'Session')->value('value') ?: DB::table('session')->where('status', '1')->value('title'),
            'electionDate' => DB::table('election_settings')->where('description', 'Election Date')->value('value') ?: now()->format('jS F, Y'),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'election-results-' . strtolower(str_replace(' ', '-', (string) ($category ?: 'all'))) . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function certificate(Request $request, int $id)
    {
        $this->guard();
        $row = $this->decorate($this->candidateQuery()->where('election_candidates.id', $id)->get())->first();
        abort_unless($row && $row->is_elected, 404, 'A certificate is only available for an elected candidate.');
        return $this->certificatePdf(collect([$row]), 'election-certificate-' . $id . '.pdf');
    }

    public function certificates(Request $request)
    {
        $this->guard();
        $rows = $this->filterRows($this->decorate($this->candidateQuery()->get()), $request)->where('is_elected', true)->values();
        if ($rows->isEmpty()) {
            return redirect()->back()->with('error', 'No elected candidates match the selected filters.');
        }
        return $this->certificatePdf($rows, 'election-certificates-' . now()->format('Ymd-His') . '.pdf');
    }

    private function certificatePdf(Collection $rows, string $filename)
    {
        set_time_limit(300);
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', public_path());
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.election-certificate', [
            'rows' => $rows,
            'session' => DB::table('election_settings')->where('description', 'Session')->value('value') ?: DB::table('session')->where('status', '1')->value('title'),
            'electionDate' => DB::table('election_settings')->where('description', 'Election Date')->value('value') ?: now()->format('jS F, Y'),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function candidateQuery()
    {
        return DB::table('election_candidates')
            ->leftJoin('faculty', 'election_candidates.faculty', '=', 'faculty.code')
            ->leftJoin('program', 'election_candidates.program', '=', 'program.code')
            ->select('election_candidates.*', 'faculty.title AS faculty_title', 'program.title AS program_title')
            ->orderBy('election_candidates.category')
            ->orderBy('election_candidates.order')
            ->orderByDesc('election_candidates.vote');
    }

    private function decorate(Collection $rows): Collection
    {
        $groups = $rows->groupBy(fn ($row) => $this->winnerKey($row));
        return $rows->map(function ($row) use ($groups) {
            $group = $groups->get($this->winnerKey($row), collect());
            $max = (int) $group->max(fn ($item) => (int) ($item->vote ?? 0));
            $isElected = $max > 0 && (int) ($row->vote ?? 0) === $max;
            $row->is_elected = $isElected;
            $row->remark = $isElected ? 'Elected' : 'Not elected';
            $row->entity_value = $this->entityValue($row);
            $row->entity_label = $this->entityLabel($row);
            return $row;
        });
    }

    private function filterRows(Collection $rows, Request $request): Collection
    {
        $category = $this->normaliseCategory($request->input('category'));
        $office = strtolower(trim((string) $request->input('office', 'all')));
        $entity = strtolower(trim((string) $request->input('entity', 'all')));
        $status = strtolower(trim((string) $request->input('status', 'all')));
        $search = strtolower(trim((string) $request->input('search', '')));
        return $rows->filter(function ($row) use ($category, $office, $entity, $status, $search) {
            $haystack = strtolower(trim(implode(' ', [
                $row->candidate ?? '', $row->name ?? '', $row->position ?? '', $row->category ?? '',
                $row->entity_label ?? '', $row->entity_value ?? '', $row->faculty_title ?? '', $row->program_title ?? '', $row->level ?? '',
            ])));
            return (!$category || strcasecmp((string) $row->category, $category) === 0)
                && ($office === '' || $office === 'all' || strtolower((string) $row->position) === $office)
                && ($entity === '' || $entity === 'all' || strtolower((string) $row->entity_value) === $entity)
                && ($status === '' || $status === 'all' || ($status === 'elected' ? $row->is_elected : !$row->is_elected))
                && ($search === '' || str_contains($haystack, $search));
        })->values();
    }

    private function entityOptions(Collection $rows, ?string $category): Collection
    {
        if ($category === 'General' || $category === null) {
            return collect();
        }
        return $rows->when($category, fn (Collection $items) => $items->where('category', $category))
            ->filter(fn ($row) => trim((string) $this->entityValue($row)) !== '')
            ->mapWithKeys(fn ($row) => [(string) $this->entityValue($row) => $this->entityLabel($row)])
            ->sort()
            ->map(fn ($label, $value) => (object) ['value' => $value, 'label' => $label])
            ->values();
    }

    private function winnerKey($row): string
    {
        $category = strtolower(trim((string) ($row->category ?? '')));
        $position = strtolower(trim((string) ($row->position ?? '')));
        $entity = strtolower(trim((string) $this->entityValue($row)));
        $level = strtolower(trim((string) ($row->level ?? '')));
        return match ($category) {
            'faculty rep' => $category . '|' . $entity . '|' . $level . '|' . $position,
            'hostel rep', 'lga rep' => $category . '|' . $entity . '|' . $position,
            default => $category . '|' . $position,
        };
    }

    private function entityValue($row): string
    {
        return match (strtolower(trim((string) ($row->category ?? '')))) {
            'faculty rep' => (string) ($row->faculty ?? ''),
            'hostel rep' => (string) ($row->hostel ?? ''),
            'lga rep' => (string) ($row->lga ?? ''),
            default => '',
        };
    }

    private function entityLabel($row): string
    {
        return match (strtolower(trim((string) ($row->category ?? '')))) {
            'faculty rep' => (string) ($row->faculty_title ?: $row->faculty ?: ''),
            'hostel rep' => (string) ($row->hostel ?? ''),
            'lga rep' => (string) ($row->lga ?? ''),
            default => '',
        };
    }

    private function normaliseCategory($category): ?string
    {
        $category = trim((string) $category);
        return $category !== '' && strtolower($category) !== 'all' && in_array($category, self::CATEGORIES, true) ? $category : null;
    }

    private function guard(): void
    {
        abort_unless(session()->has('log') && in_array(session('accType'), ['Admin', 'Staff'], true), 403);
    }
}
