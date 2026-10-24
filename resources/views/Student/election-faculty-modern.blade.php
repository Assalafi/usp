@php
    $student = DB::table('students')->where('user_id', session('id'))->first(['faculty','program','duration','level','vflag','school_fee','status','profile_status','profile_submitted_at','profile_submission_session','profile_level_session']);
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $profileReady = $student && (int) $student->status === 1 && $student->profile_status === 'submitted' && !empty($student->profile_submitted_at) && (string) $student->profile_submission_session === (string) $currentSession && (string) $student->profile_level_session === (string) $currentSession;
    $faculty = $student->faculty ?? session('faculty');
    $level = $student->level ?? session('level');
    $status = DB::table('election_settings')->where('description', 'Election Flag')->value('value');
    $flag = 1;
    $school_fee = 1;
    $vote = DB::table('election_votes')->where(['username' => session('id_number'), 'category' => $category])->select('id')->first();
    $positions = $poss->where('category', $category)->where('position', $level);
    $visibleCandidates = $data->where('category', $category)->where('acc_type', 'Student')->where('faculty', $faculty)->where('level', $level);
    $eligibility = $flag == 1 && $school_fee == 1 && !empty($faculty) && !empty($level) && $profileReady;
    $eligibilityMessage = !$profileReady ? 'Update and submit your student profile for the current session before voting.' : (empty($faculty) || empty($level) ? 'Your faculty or current level is not available on your student record.' : 'Please complete the required voter accreditation and fee requirements.');
    $scopeLabel = trim(($faculty ?: 'Faculty') . ' · ' . ($level ?: 'Current level'));
@endphp
@include('Student.election-voting')
