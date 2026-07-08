@php
    $flag = DB::table('staff')->select('degree')->where('user_id', session('id'))->value('degree');
    //$school_fee = DB::table('students')->select('school_fee')->where('user_id', session('id'))->value('school_fee');
    //$flag = 1;
    $school_fee = 1;
    $status = DB::table('election_settings')->select('value')->where('description', 'Election Flag')->value('value');
    $vState = DB::table('election_settings')->select('value')->where('description', 'State')->value('value');
    $vType = DB::table('election_settings')->select('value')->where('description', 'Type')->value('value');
    $vState = strtoupper($vState);
    $vType = strtoupper($vType);
    $vote = DB::table('election_votes')
        ->where(['username' => session('username'), 'category' => $category])
        ->select('id')
        ->first();
@endphp
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
    :root {
        --primary: #3da1e3;
        --primary-dark: #2c7bb8;
        --primary-light: #e8f4fd;
        --surface: #ffffff;
        --surface-variant: #f8fafc;
        --on-surface: #1e293b;
        --on-surface-variant: #64748b;
        --outline: #e2e8f0;
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius: 12px;
        --radius-lg: 16px;
        --radius-xl: 20px;
    }

    * {
        font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    .main-body {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Modern card system */
    .card {
        background: var(--surface);
        border: 1px solid var(--outline);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        margin-bottom: 20px;
        position: relative;
    }

    .card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
        border-color: var(--primary);
    }

    /* Candidate cards */
    .card-deck .card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        margin-bottom: 16px;
    }

    .card-deck .card:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .card-body {
        padding: 24px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    /* Profile photos */
    .card-img-top {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--outline);
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
    }

    .card:hover .card-img-top {
        border-color: var(--primary);
        transform: scale(1.05);
        box-shadow: 0 8px 25px -8px var(--primary);
    }

    /* Typography */
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--on-surface);
        margin: 0;
        line-height: 1.4;
    }

    /* Modern checkbox */
    .form-check {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 8px;
    }

    .form-check-input {
        width: 24px;
        height: 24px;
        border: 2px solid var(--outline);
        border-radius: 6px;
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s ease;
        appearance: none;
        position: relative;
        margin: 0;
    }

    .form-check-input:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(61 161 227 / 0.1);
    }

    .form-check-input:checked {
        background: var(--primary);
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(61 161 227 / 0.2);
    }

    .form-check-input:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: 700;
    }

    /* Selected state */
    .selected-card {
        border-color: var(--primary) !important;
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--surface) 100%) !important;
        transform: translateY(-4px) !important;
        box-shadow: 0 20px 25px -5px rgb(61 161 227 / 0.2), 0 8px 10px -6px rgb(61 161 227 / 0.1) !important;
    }

    .selected-card .card-img-top {
        border-color: var(--primary) !important;
        box-shadow: 0 8px 25px -8px var(--primary) !important;
    }

    .selected-card .card-title {
        color: var(--primary-dark) !important;
        font-weight: 700 !important;
    }

    /* Navigation tabs */
    .nav-pills {
        background: var(--surface);
        border: 1px solid var(--outline);
        border-radius: var(--radius-xl);
        padding: 6px;
        margin-bottom: 24px;
        display: flex;
        gap: 4px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .nav-pills .nav-link {
        background: transparent;
        color: var(--on-surface-variant);
        border: none;
        border-radius: var(--radius-lg);
        padding: 12px 20px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .nav-pills .nav-link:hover {
        background: var(--surface-variant);
        color: var(--on-surface);
    }

    .nav-pills .nav-link.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 6px -1px rgb(61 161 227 / 0.3);
    }

    /* Card grid */
    .card-deck {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 0;
    }

    /* Submit button */
    .btn-info {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: var(--radius-lg);
        padding: 16px 32px;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgb(61 161 227 / 0.3);
        cursor: pointer;
        text-transform: none;
        letter-spacing: 0;
    }

    .btn-info:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgb(61 161 227 / 0.4);
    }

    .btn-info:active {
        transform: translateY(0);
    }

    /* Headers */
    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        padding: 24px;
        font-weight: 600;
        font-size: 1.25rem;
        text-align: center;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    /* Alerts */
    .alert-info {
        background: var(--primary-light);
        border: 1px solid var(--primary);
        border-radius: var(--radius-lg);
        color: var(--primary-dark);
        padding: 16px 20px;
        font-weight: 500;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .main-body {
            padding: 12px;
        }
        
        .card-deck {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .nav-pills {
            margin-bottom: 20px;
        }
        
        .nav-pills .nav-link {
            padding: 10px 16px;
            font-size: 0.85rem;
        }
        
        .card-body {
            padding: 20px 16px;
        }
        
        .card-img-top {
            width: 80px;
            height: 80px;
        }
        
        .card-title {
            font-size: 1rem;
        }
        
        .btn-info {
            padding: 14px 28px;
            font-size: 0.95rem;
        }
        
        .card-header {
            padding: 20px 16px;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 480px) {
        .main-body {
            padding: 8px;
        }
        
        .card-deck {
            gap: 12px;
        }
        
        .card-body {
            padding: 16px 12px;
            gap: 12px;
        }
        
        .card-img-top {
            width: 70px;
            height: 70px;
        }
        
        .card-title {
            font-size: 0.95rem;
        }
        
        .form-check-input {
            width: 22px;
            height: 22px;
        }
        
        .nav-pills .nav-link {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
    }

    /* Form labels */
    .form-check-label {
        cursor: pointer;
        user-select: none;
        width: 100%;
        border-radius: var(--radius-lg);
        transition: all 0.2s ease;
    }

    .form-check-label:hover {
        background: var(--surface-variant);
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--surface-variant);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--outline);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--on-surface-variant);
    }

    /* Focus states for accessibility */
    .form-check-input:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .nav-pills .nav-link:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .btn-info:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    /* Ultra-simple mobile results */
    .mobile-results {
        max-width: 100%;
        margin: 0;
        padding: 5px;
    }

    .result-position {
        background: white;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .position-title {
        background: var(--primary);
        color: white;
        padding: 12px 15px;
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .candidate-list {
        padding: 0;
    }

    .candidate-row {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .candidate-row:last-child {
        border-bottom: none;
    }

    .rank-badge {
        width: 20px;
        height: 20px;
        background: #666;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        flex-shrink: 0;
    }

    .rank-badge.first { background: #gold; }
    .rank-badge.second { background: #silver; }
    .rank-badge.third { background: #cd7f32; }

    .candidate-photo {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }

    .candidate-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .candidate-info {
        flex: 1;
        min-width: 0;
    }

    .candidate-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vote-count {
        font-size: 0.75rem;
        color: #666;
    }

    .vote-percentage {
        background: var(--primary);
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 35px;
        text-align: center;
    }

    .total-summary {
        background: #f8f9fa;
        padding: 8px 15px;
        text-align: center;
        font-size: 0.75rem;
        color: #666;
        border-top: 1px solid #eee;
    }
</style>
@if ($flag == 1)
    @if ($school_fee == 1)
        @if ($flag == 0)
            <div class="alert alert-info">
                <h3>Goto NUGA Hall to Accreditate your account</h3>
            </div>
        @endif
        @if ($flag == 1)
            {{-- <div class="alert alert-info">
            <h3>You are set for Voting, wait for Election date</h3>
        </div> --}}
            @if (!$vote && strtoupper($status) == 'OPEN' && strtoupper($vType) == 'STAFF')
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ strtoupper($page) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <form action="/voting" method="POST">
                                    @csrf
                                    <input type="hidden" name="category" id="category" value="{{ $category }}">
                                    <div class="card">
                                        <div class="card-block">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                @php
                                                    $active = 1;
                                                @endphp
                                                @foreach ($poss->where('category', $category) as $pos)
                                                    <li class="nav-item">
                                                        <a class="nav-link @if ($active == 1) active @endif"
                                                            id="pills-{{ $pos->id }}-tab" data-bs-toggle="pill"
                                                            href="#pills-{{ $pos->id }}" role="tab"
                                                            aria-controls="pills-{{ $pos->id }}"
                                                            aria-selected="true">{{ $pos->position }}</a>
                                                    </li>
                                                    @php
                                                        $active = 2;
                                                    @endphp
                                                @endforeach
                                                <li class="nav-item">
                                                    <a class="nav-link" id="pills-submit-tab" data-bs-toggle="pill"
                                                        href="#pills-submit" role="tab" aria-controls="pills-submit"
                                                        aria-selected="true">Submit</a>
                                                </li>

                                            </ul>
                                            <div class="tab-content" id="pills-tabContent">
                                                @php
                                                    $active = 1;
                                                @endphp
                                                @foreach ($poss->where('category', $category) as $pos)
                                                    <div class="tab-pane fade @if ($active == 1) show active @endif"
                                                        id="pills-{{ $pos->id }}" role="tabpanel"
                                                        aria-labelledby="pills-{{ $pos->id }}-tab">
                                                        <!-- [ Data table ] start -->
                                                        <div class="card-deck row">
                                                            @foreach ($data->where('category', $category)->where('position', $pos->position) as $row)
                                                                <div class="card col-md-3 shadow card_{{ $pos->id }}"
                                                                    id="card_{{ $row->id }}">

                                                                    <label class="form-check-label"
                                                                        for="candidate_{{ $row->id }}">
                                                                        <center>
                                                                            <img src="{{ asset('storage/picture/' . $row->picture) }}"
                                                                                class="card-img-top img-radius img-fluid wid-80"
                                                                                alt="{{ $row->picture }}">
                                                                        </center>

                                                                        <div class="card-body">
                                                                            <h5 class="card-title">{{ $row->name }}
                                                                            </h5>
                                                                            {{-- <p class="card-title">
                                                                                <strong>{{ $row->candidate }}</strong>
                                                                            </p>
                                                                            <p class="card-text">{{ $row->level }}
                                                                                Level
                                                                            </p> --}}
                                                                            <p class="card-text">
                                                                                {{ $row->program_title }}
                                                                            </p>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox"
                                                                                    name="{{ $pos->position }}[]"
                                                                                    value="{{ $row->candidate }}"
                                                                                    id="candidate_{{ $row->id }}"
                                                                                    onclick="selectCandidate('{{ $row->id }}', '{{ $pos->id }}')">
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <!-- [ Data table ] end -->
                                                    </div>
                                                    @php
                                                        $active = 2;
                                                    @endphp
                                                @endforeach
                                                <div class="tab-pane fade" id="pills-submit" role="tabpanel"
                                                    aria-labelledby="pills-submit-tab">
                                                    <!-- [ Data table ] start -->

                                                    <div class="card text-center">
                                                        <div class="card-header">
                                                            <h4>Submit Vote</h4>
                                                            <div class="alert alert-info">
                                                                Once you submit your vote, you will not be able to vote
                                                                again
                                                                for this category.
                                                                <br>
                                                                Yes i agree <input type="checkbox" name=""
                                                                    id="" required>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <Button style="width: 100%" type="submit"
                                                                    class="btn btn-info">All set, Submit</Button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- [ Data table ] end -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            @else
                @if (strtoupper($status) == 'OPEN' && strtoupper($vType) == 'STAFF')
                    <div class="card" style="max-width: 600px; margin: 40px auto;">
                        <div class="card-body" style="padding: 40px; text-align: left; gap: 0;">
                            <div style="display: flex; align-items: center; margin-bottom: 24px;">
                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                                    <svg width="28" height="28" fill="white" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: var(--on-surface); font-weight: 600; font-size: 1.5rem;">Vote Submitted Successfully!</h3>
                                    <p style="margin: 4px 0 0 0; color: var(--on-surface-variant); font-size: 0.9rem;">Thank you for participating</p>
                                </div>
                            </div>
                            
                            <div style="background: var(--primary-light); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px;">
                                <h4 style="margin: 0 0 12px 0; color: var(--primary-dark); font-weight: 600; font-size: 1.1rem;">
                                    Dear {{ session('name') }},
                                </h4>
                                <p style="margin: 0; color: var(--on-surface); line-height: 1.6; font-size: 1rem;">
                                    We are pleased to inform you that the results of the election will be available immediately after the voting ends.
                                </p>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: center; padding: 20px; background: var(--surface-variant); border-radius: var(--radius-lg);">
                                <svg width="20" height="20" fill="var(--primary)" viewBox="0 0 24 24" style="margin-right: 12px;">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <span style="color: var(--on-surface); font-weight: 500; font-size: 0.95rem;">
                                    Thank you for participating in this important democratic process.
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mobile-results">
                        @foreach ($poss->where('category', $category) as $pos)
                            <div class="result-position">
                                <h5 class="position-title">{{ $pos->position }}</h5>
                                <div class="candidate-list">
                                    @php
                                        $candidates = $data->where('category', $category)->where('position', $pos->position);
                                        $totalVotes = $candidates->sum('vote');
                                        $sn = 1;
                                    @endphp
                                    
                                    @foreach ($candidates as $row)
                                        @php
                                            $percentage = $totalVotes > 0 ? round(($row->vote / $totalVotes) * 100, 1) : 0;
                                            $rankClass = '';
                                            if ($sn == 1) $rankClass = 'first';
                                            elseif ($sn == 2) $rankClass = 'second';
                                            elseif ($sn == 3) $rankClass = 'third';
                                        @endphp
                                        <div class="candidate-row">
                                            <div class="rank-badge {{ $rankClass }}">{{ $sn++ }}</div>
                                            <div class="candidate-photo">
                                                <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="{{ $row->name }}">
                                            </div>
                                            <div class="candidate-info">
                                                <div class="candidate-name">{{ $row->name }}</div>
                                                <div class="vote-count">{{ number_format($row->vote) }} votes</div>
                                            </div>
                                            <div class="vote-percentage">{{ $percentage }}%</div>
                                        </div>
                                    @endforeach
                                    
                                    @if($totalVotes > 0)
                                        <div class="total-summary">
                                            Total: {{ number_format($totalVotes) }} votes cast
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        @endif
    @else
        <div class="alert alert-info">
            <h3>Only student that pays his school fees is eligible for Election, go and pay your school fees</h3>
        </div>
    @endif
@else
    <div class="alert alert-info">
        <h3>You are not eligible for this Election</h3>
    </div>
@endif


<script>
    //alert('i');
    function selectCandidate(candidateId, positionId) {
        const checkbox = document.getElementById('candidate_' + candidateId);
        const selectedCheckboxes = document.querySelectorAll('input[name="' + document.querySelector('#candidate_' +
            candidateId).name + '"]:checked');

        // If trying to check more than 2 candidates
        if (checkbox.checked && selectedCheckboxes.length > 2) {
            checkbox.checked = false;
            //alert('You can only select a maximum of 2 candidates for this position.');
            // swal
            swal({
                title: 'Warning!',
                text: 'You can only select a maximum of 2 candidates for this position.',
                icon: 'warning',
                button: 'OK',
            });
            return;
        }

        // Toggle the selected-card class based on checkbox state
        const card = document.getElementById('card_' + candidateId);
        if (checkbox.checked) {
            card.classList.add('selected-card');
        } else {
            card.classList.remove('selected-card');
        }
    }
</script>
