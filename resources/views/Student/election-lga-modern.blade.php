@php
    $student = DB::table('students')->where('user_id', session('id'))->first(['faculty','program','duration','level','vflag','school_fee','status','profile_status','profile_submitted_at','profile_submission_session','profile_level_session']);
    $currentSession = session('system_session') ?: DB::table('session')->where('status', '1')->value('title');
    $lga = session('lga');
    $status = DB::table('election_settings')->where('description', 'Election Flag')->value('value');
    $flag = 1;
    $school_fee = 1;
    $vote = DB::table('election_votes')->where(['username' => session('id_number'), 'category' => $category])->select('id')->first();
    $positions = $poss->where('category', $category);
    $visibleCandidates = $data->where('category', $category)->where('acc_type', 'Student')->where('lga', $lga);
    $profileReady = $student && (int) $student->status === 1 && $student->profile_status === 'submitted' && !empty($student->profile_submitted_at) && (string) $student->profile_submission_session === (string) $currentSession && (string) $student->profile_level_session === (string) $currentSession;
    $eligibility = $flag == 1 && $school_fee == 1 && !empty($lga) && $profileReady;
    $eligibilityMessage = !$profileReady ? 'Update and submit your student profile for the current session before voting.' : (empty($lga) ? 'Your local government area is not available on your student record.' : 'Please complete the required voter accreditation and fee requirements.');
    $scopeLabel = $lga ? 'LGA: ' . $lga : 'LGA representative';
@endphp
@include('Student.election-voting')
