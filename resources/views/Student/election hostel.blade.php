@php
    $record = DB::table('students')
        ->select('vflag')
        ->where('user_id', session('id'))
        ->select('vflag', 'faculty', 'level', 'school_fee')
        ->get();
    foreach ($record as $record) {
        $flag = 1;
        $faculty = $record->faculty;
        $level = $record->level;
        $school_fee = 1;
    }
    //$school_fee = DB::table('students')->select('school_fee')->where('user_id', session('id'))->value('school_fee');
    $status = DB::table('election_settings')->select('value')->where('description', 'Election Flag')->value('value');
    $hostel = DB::table('hostel')->select('hall')->where('occupant', session('id_number'))->value('hall');
    $vote = DB::table('election_votes')
        ->where(['username' => session('id_number'), 'category' => $category])
        ->select('id')
        ->first();
@endphp
<style>
    .card-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .card-img-top {
        width: 150px;
        height: 150px;
        object-fit: cover;
    }

    .form-check {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-check-input {
        width: 40px;
        height: 40px;
    }

    .form-check-label {
        margin-left: 8px;
        font-size: 1.2rem;
        /* Adjust the size of the label text if needed */
    }

    .selected-card {
        border: 5px solid rgb(62, 161, 228);
        /* Add any desired styling for selected cards */
        background-color: #e4dbf7;
        /* Light green background */
    }
</style>
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
        @if (!$vote && strtoupper($status) == 'OPEN')
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
                                                        @foreach ($data->where('category', $category)->where('hostel', $hostel) as $row)
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
                                                                        <h5 class="card-title">{{ $row->name }}</h5>
                                                                        <p class="card-title">
                                                                            <strong>{{ $row->candidate }}</strong>
                                                                        </p>
                                                                        <p class="card-text">{{ $row->level }} Level
                                                                        </p>
                                                                        <p class="card-text">{{ $row->program_title }}
                                                                        </p>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                type="radio"
                                                                                name="{{ $pos->position }}"
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
            @if (strtoupper($status) == 'OPEN')
                <h5>
                    Dear Voter {{ session('name') }},
                    <br><br>
                    We are pleased to inform you that the results of the election will be available immediately after
                    the voting ends.
                    <br><br>
                    Thank you for participating in this important process.
                </h5>
            @else
                <div class="row">
                    @foreach ($poss->where('category', $category) as $pos)
                        <!-- [ Data table ] start -->

                        <div class="card col-md-6 shadow">
                            <div class="card-header">
                                <h5 class="card-title">{{ $pos->position }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id=""
                                        class="display table nowrap table-striped table-hover table-responsive">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ 'Candidate' }}</th>
                                                <th>{{ 'Name' }}</th>
                                                <th>{{ 'Vote' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sn = 1;
                                            @endphp
                                            @foreach ($data->where('category', $category)->where('hostel', $hostel) as $row)
                                                <tr>
                                                    <td>{{ $sn++ }}</td>
                                                    <td>{{ $row->candidate }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->vote }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- [ Data table ] end -->
                        @php
                            $active = 2;
                        @endphp
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

<script>
    //alert('i');
    function selectCandidate(candidateId, positionId) {
        //alert('Hii');
        // Remove 'selected-card' class from all cards in the same position

        const allCards = document.querySelectorAll('.card_' + positionId);
        allCards.forEach(card => {
            card.classList.remove('selected-card');
        });

        // Add 'selected-card' class to the selected card
        document.getElementById('card_' + candidateId).classList.add('selected-card');
    }
</script>
