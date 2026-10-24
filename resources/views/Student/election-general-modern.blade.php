@php
    $status = DB::table('election_settings')->where('description', 'Election Flag')->value('value');
    $vState = strtoupper((string) DB::table('election_settings')->where('description', 'State')->value('value'));
    $vType = strtoupper((string) DB::table('election_settings')->where('description', 'Type')->value('value'));
    $student = DB::table('students')->where('user_id', session('id'))->first(['faculty','level','vflag','school_fee','status','profile_status','profile_submitted_at','profile_submission_session','profile_level_session']);
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $profileReady = $student && (int) $student->status === 1 && $student->profile_status === 'submitted' && !empty($student->profile_submitted_at) && (string) $student->profile_submission_session === (string) $currentSession && (string) $student->profile_level_session === (string) $currentSession;
    $flag = 1;
    $school_fee = 1;
    $vote = DB::table('election_votes')->where(['username' => session('id_number'), 'category' => $category])->select('id')->first();
    $positions = $poss->where('category', $category);
    $visibleCandidates = $data->where('category', $category)->where('acc_type', session('accType'));
    $stateAllowed = $vState === 'ALL' || strtoupper((string) session('state')) === $vState;
    $eligibility = $flag == 1 && $school_fee == 1 && $stateAllowed && $vType === 'STUDENT' && $profileReady;
    $eligibilityMessage = !$profileReady ? 'Update and submit your student profile for the current session before voting.' : (!$stateAllowed ? 'This election is limited to voters from the configured state.' : ($vType !== 'STUDENT' ? 'This ballot is not currently open to students.' : 'Please complete the required voter accreditation and fee requirements.'));
    $scopeLabel = 'All students';
@endphp
@include('Student.election-voting')
