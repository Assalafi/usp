<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ElectionVotingController extends Controller
{
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace('create ', '', $contents);
        $contents = str_replace('upload ', '', $contents);
        $contents = str_replace('download ', '', $contents);
        $contents = str_replace('update ', '', $contents);
        $contents = str_replace('delete ', '', $contents);
        $this->page = $contents;
        $this->table = str_replace(' ', '_', $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table($this->table)->orderBy('order', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function live()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table('election_candidates')->select('election_candidates.*', 'program.title AS program_title', 'faculty.title AS faculty_title')->leftJoin('program', function ($join) {
            $join->on('election_candidates.program', '=', 'program.code');
        })->leftJoin('faculty', function ($join) {
            $join->on('election_candidates.faculty', '=', 'faculty.code');
        })->orderBy('election_candidates.vote', 'DESC')->get();
        $data['positions'] = DB::table('election_positions')->orderBy('order', 'ASC')->get();
        $data['category'] = 'General';
        $data['liveStats'] = $this->liveStats();
        $data['page'] = 'election live';
        $data['title'] = 'election live';
        return view('main', $data);
    }

    /**
     * Lightweight feed for the live election wall. The dashboard polls this
     * endpoint instead of reloading the page, so vote totals remain current
     * while the four panels continue rotating.
     */
    public function liveData()
    {
        if (!session()->has('log')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $candidates = DB::table('election_candidates')
            ->select('election_candidates.*', 'program.title AS program_title', 'faculty.title AS faculty_title')
            ->leftJoin('program', function ($join) {
                $join->on('election_candidates.program', '=', 'program.code');
            })->leftJoin('faculty', function ($join) {
                $join->on('election_candidates.faculty', '=', 'faculty.code');
            })
            ->orderByDesc('election_candidates.vote')
            ->get();

        $positions = DB::table('election_positions')
            ->orderBy('order', 'ASC')
            ->get();

        return response()->json([
            'status' => strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value')),
            'candidates' => $candidates,
            'positions' => $positions,
            'stats' => $this->liveStats(),
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Counts only students who are active and have submitted the current
     * session profile. The faculty and hostel counts are scoped to the same
     * eligibility rules used by the student voting screens.
     */
    private function liveStats(): array
    {
        $eligible = $this->eligibleStudentsQuery();
        $eligibleUsernames = $this->eligibleStudentsQuery()->select('username');
        $generalAccredited = (clone $eligible)->count();
        $generalVoted = DB::table('election_votes')
            ->where('category', 'General')
            ->whereIn('username', $eligibleUsernames)
            ->distinct()
            ->count('username');

        $facultyAccredited = (clone $eligible)
            ->select('faculty', 'level', DB::raw('COUNT(DISTINCT username) AS total'))
            ->groupBy('faculty', 'level')
            ->get();
        $facultyVoted = DB::table('election_votes AS votes')
            ->join('students', 'students.username', '=', 'votes.username')
            ->where('votes.category', 'Faculty Rep')
            ->whereIn('students.username', $eligibleUsernames)
            ->select('students.faculty', 'students.level', DB::raw('COUNT(DISTINCT votes.username) AS total'))
            ->groupBy('students.faculty', 'students.level')
            ->get();

        $hostelAccredited = (clone $eligible)
            ->join('hostel', 'hostel.occupant', '=', 'students.username')
            ->select('hostel.hall', DB::raw('COUNT(DISTINCT students.username) AS total'))
            ->groupBy('hostel.hall')
            ->get();
        $hostelVoted = DB::table('election_votes AS votes')
            ->join('students', 'students.username', '=', 'votes.username')
            ->join('hostel', 'hostel.occupant', '=', 'students.username')
            ->where('votes.category', 'Hostel Rep')
            ->whereIn('students.username', $eligibleUsernames)
            ->select('hostel.hall', DB::raw('COUNT(DISTINCT votes.username) AS total'))
            ->groupBy('hostel.hall')
            ->get();

        $facultyVotedMap = $facultyVoted->mapWithKeys(fn ($row) => [strtolower(trim((string) $row->faculty) . '|' . trim((string) $row->level)) => (int) $row->total]);
        $facultyStats = $facultyAccredited->mapWithKeys(function ($row) use ($facultyVotedMap) {
            $key = strtolower(trim((string) $row->faculty) . '|' . trim((string) $row->level));
            $accredited = (int) $row->total;
            $voted = (int) ($facultyVotedMap->get($key, 0));
            return [$key => ['accredited' => $accredited, 'voted' => $voted, 'remaining' => max(0, $accredited - $voted)]];
        })->all();

        $hostelVotedMap = $hostelVoted->mapWithKeys(fn ($row) => [strtolower(trim((string) $row->hall)) => (int) $row->total]);
        $hostelStats = $hostelAccredited->mapWithKeys(function ($row) use ($hostelVotedMap) {
            $key = strtolower(trim((string) $row->hall));
            $accredited = (int) $row->total;
            $voted = (int) ($hostelVotedMap->get($key, 0));
            return [$key => ['accredited' => $accredited, 'voted' => $voted, 'remaining' => max(0, $accredited - $voted)]];
        })->all();

        return [
            'general' => ['accredited' => $generalAccredited, 'voted' => $generalVoted, 'remaining' => max(0, $generalAccredited - $generalVoted)],
            'faculty' => $facultyStats,
            'hostel' => $hostelStats,
        ];
    }

    private function eligibleStudentsQuery()
    {
        $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
        $query = DB::table('students')
            ->leftJoin('program as election_program', 'election_program.code', '=', 'students.program')
            ->where('students.status', 1)
            ->where('students.profile_status', 'submitted')
            ->whereNotNull('students.profile_submitted_at')
            // A final-year student may view published results, but is not an
            // accredited voter. Prefer the programme duration and fall back
            // to the duration copied onto the student record.
            ->whereRaw("(COALESCE(NULLIF(election_program.duration, ''), NULLIF(students.duration, '')) IS NULL OR CAST(students.level AS UNSIGNED) < (CAST(COALESCE(NULLIF(election_program.duration, ''), NULLIF(students.duration, '')) AS UNSIGNED) * 100))");
        if ($currentSession) {
            $query->where('students.profile_submission_session', $currentSession)
                ->where('students.profile_level_session', $currentSession);
        } else {
            $query->whereRaw('1 = 0');
        }
        return $query;
    }

    private function isFinalYearStudent($student): bool
    {
        if (!$student) {
            return false;
        }

        $duration = (int) ($student->duration ?? 0);
        if ($duration <= 0 && !empty($student->program)) {
            $duration = (int) DB::table('program')->where('code', $student->program)->value('duration');
        }

        $level = (int) preg_replace('/[^0-9]/', '', (string) ($student->level ?? ''));

        return $duration > 0 && $level > 0 && $level >= ($duration * 100);
    }

    public function voting(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $category = trim((string) $request->input('category'));
        $voter = trim((string) session('id_number', session('username')));
        if ($category === '' || $voter === '') {
            return redirect()->back()->with('error', 'We could not identify your election ballot. Please sign in again.');
        }
        if (session('accType') === 'Student') {
            $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
            $student = DB::table('students')->where('user_id', session('id'))->first(['status', 'program', 'duration', 'level', 'profile_status', 'profile_submitted_at', 'profile_submission_session', 'profile_level_session']);
            $profileReady = $student && (int) $student->status === 1 && $student->profile_status === 'submitted' && !empty($student->profile_submitted_at) && (string) $student->profile_submission_session === (string) $currentSession && (string) $student->profile_level_session === (string) $currentSession;
            if (!$profileReady) {
                return redirect()->back()->with('error', 'Update and submit your student profile for the current session before voting.');
            }
            if ($this->isFinalYearStudent($student)) {
                return redirect()->back()->with('error', 'Final-year students are not eligible to vote in this election. Published results will be available after voting closes.');
            }
        }
        if (strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value')) !== 'OPEN') {
            return redirect()->back()->with('error', 'Voting is currently closed.');
        }
        if (!$request->boolean('agreement')) {
            return redirect()->back()->with('error', 'Please confirm your selections before submitting your vote.');
        }

        // A MySQL advisory lock closes the race window between two simultaneous
        // submissions from the same voter (for example, two browser tabs).
        $lockName = 'unimaid:election:' . sha1($voter . '|' . $category);
        $lock = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);
        if (!$lock || (int) ($lock->acquired ?? 0) !== 1) {
            return redirect()->back()->with('error', 'Your ballot is busy processing. Please try again.');
        }

        try {
            return DB::transaction(function () use ($request, $category, $voter) {
                // Re-check the switch inside the transaction so an administrator
                // closing the election cannot overlap a ballot being committed.
                if (strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value')) !== 'OPEN') {
                    return redirect()->back()->with('error', 'Voting is currently closed.');
                }
                if (session('accType') === 'Student') {
                    $student = DB::table('students')
                        ->where('user_id', session('id'))
                        ->lockForUpdate()
                        ->first(['status', 'program', 'duration', 'level', 'profile_status', 'profile_submitted_at', 'profile_submission_session', 'profile_level_session']);
                    if ($this->isFinalYearStudent($student)) {
                        return redirect()->back()->with('error', 'Final-year students are not eligible to vote in this election.');
                    }
                }
                if (DB::table('election_votes')->where(['username' => $voter, 'category' => $category])->lockForUpdate()->exists()) {
                    return redirect()->back()->with('error', 'Your vote for this election has already been submitted.');
                }

                $positions = DB::table('election_positions')->where('category', $category)->orderBy('order')->pluck('position');
                $candidateRows = DB::table('election_candidates')
                    ->where('category', $category)
                    ->whereIn('position', $positions->all())
                    ->get(['candidate', 'position']);
                $allowed = [];
                $hasCandidates = [];
                foreach ($candidateRows as $candidateRow) {
                    $positionKey = (string) $candidateRow->position;
                    $candidateKey = (string) $candidateRow->candidate;
                    $allowed[$positionKey . "\0" . $candidateKey] = true;
                    $hasCandidates[$positionKey] = true;
                }
                $selections = [];
                $missing = [];
                foreach ($positions as $position) {
                    $keyVariants = [$position, str_replace(' ', '_', $position)];
                    $candidate = null;
                    foreach ($keyVariants as $key) {
                        if ($request->filled($key)) {
                            $candidate = trim((string) $request->input($key));
                            break;
                        }
                    }
                    if ($candidate === null || $candidate === '') {
                        if (!empty($hasCandidates[(string) $position])) {
                            $missing[] = $position;
                        }
                        continue;
                    }
                    if (empty($allowed[(string) $position . "\0" . (string) $candidate])) {
                        $missing[] = $position;
                        continue;
                    }
                    $selections[] = ['position' => $position, 'candidate' => $candidate];
                }
                if ($missing) {
                    return redirect()->back()->with('error', 'Please select one candidate for: ' . implode(', ', $missing) . '.');
                }
                if (!$selections) {
                    return redirect()->back()->with('error', 'Please select at least one valid candidate before submitting.');
                }
                $selectedRows = DB::table('election_candidates')
                    ->where('category', $category)
                    ->where(function ($query) use ($selections) {
                        foreach ($selections as $selection) {
                            $query->orWhere(function ($item) use ($selection) {
                                $item->where('position', $selection['position'])
                                    ->where('candidate', $selection['candidate']);
                            });
                        }
                    })
                    ->lockForUpdate()
                    ->get(['candidate', 'position']);
                $selectedKeys = $selectedRows->mapWithKeys(fn ($row) => [(string) $row->position . "\0" . (string) $row->candidate => true]);
                foreach ($selections as $selection) {
                    if (!$selectedKeys->has((string) $selection['position'] . "\0" . (string) $selection['candidate'])) {
                        throw new \RuntimeException('A selected candidate is no longer available.');
                    }
                }
                $timestamp = now();
                $voteRows = collect($selections)->map(fn ($selection) => [
                    'username' => $voter,
                    'candidate' => $selection['candidate'],
                    'category' => $category,
                    'position' => $selection['position'],
                    'acc_type' => session('accType'),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();
                if (!DB::table('election_votes')->insert($voteRows)) {
                    throw new \RuntimeException('The ballot could not be saved.');
                }
                $updated = DB::table('election_candidates')
                    ->where('category', $category)
                    ->where(function ($query) use ($selections) {
                        foreach ($selections as $selection) {
                            $query->orWhere(function ($item) use ($selection) {
                                $item->where('position', $selection['position'])
                                    ->where('candidate', $selection['candidate']);
                            });
                        }
                    })
                    ->increment('vote', 1);
                if ((int) $updated !== count($selections)) {
                    throw new \RuntimeException('The vote totals could not be updated.');
                }
                return redirect()->back()->with('success', 'Your vote has been submitted successfully.');
            }, 3);
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', 'We could not save your ballot safely. No vote was recorded. Please try again.');
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];

        // $check = DB::table($this->table)->where(['id' => $id])->select('no', 'no')->get();
        // foreach ($check as $row) {
        //     if($no != $row -> no){
        //         $checks = DB::table($this->table)->where(['no' => $no])->select('id')->value('id');
        //         if($checks > 0){
        //             return redirect()->back()->with('error', 'Record Already Exist!!!');
        //         }
        //     }
        // }
        unset($datas['id']);
        unset($datas['_token']);
        // $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
