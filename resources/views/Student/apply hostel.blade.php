@php
    $flagO = $flag;
    function apply($idNumber)
    {
        $duration = session('duration');
        $level = session('current_level');
        $faculty = session('faculty');
        $idPrefix = (int) substr($idNumber, 0, 2);
        $reasons = [];
        if ($duration == 4 && $idPrefix >= 20 && $level < 400) {
            return true;
        }
        if ($duration == 5 && $idPrefix >= 19 && $level < 500) {
            return true;
        }
        if ($duration == 6 && $idPrefix >= 22 && $level < 400) {
            return true;
        }
        if ($duration == 6 && $idPrefix >= 18 && $level < 600 && $faculty == 'VET') {
            return true;
        }

        // Check eligibility based on the conditions
        if ($duration == 4) {
            if ($idPrefix < 20) {
                $reasons[] = 'Your ID number must start with 20 or higher for a 4-year program.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your level must be less than 400 for a 4-year program.';
            }
        } elseif ($duration == 5) {
            if ($idPrefix < 19) {
                $reasons[] = 'Your ID number must start with 19 or higher for a 5-year program.';
            }
            if ($level >= 500) {
                $reasons[] = 'Your level must be less than 500 for a 5-year program.';
            }
        } elseif ($duration == 6) {
            if ($idPrefix < 22) {
                $reasons[] = 'Your ID number must start with 22 or higher for a 6-year program.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your level must be less than 400 for a 6-year program.';
            }
        } elseif ($duration == 6 && $faculty == 'VET') {
            if ($idPrefix < 18) {
                $reasons[] = 'Your ID number must start with 18 or higher for a 6-year program.';
            }
            if ($level >= 600) {
                $reasons[] = 'Your level must be less than 600 for a 6-year program.';
            }
        } else {
            $reasons[] = 'Invalid program duration. Only 4, 5, or 6 years are supported.';
        }

        //return false;
        $flag = 5;
        return [
            'eligible' => false,
            'message' => 'You are not eligible to apply for a hostel for the following reasons:',
            'reasons' => $reasons,
        ];
    }

    $check = apply(session('id_number'));

    if (isset($check['eligible'])) {
        $flag = $flagO;
    }

    if (session('id_number') == '19/07/04/019') {
        $flag = $flagO;
    } else {
        //$flag = 6;
        $flag = $flagO;
    }
    //$flag = 4;
@endphp
@if ($flag == 0)
    <!-- Start Content-->
    <div class="card">
        <div class="card-body text-center">
            <h3 class="mb-4">Reserve Bed Space</h3>
            <!-- Form Start -->
            <div id="mainbody">
                @if (DB::table('hostel_pin')->select('id', 'username')->where('username', session('id_number'))->first())
                    @if (count($hall) > 0)
                        <form method="POST" action="reserve bed">
                            @csrf
                            <div class="input-group mb-3">
                                <select class="form-control" id="hall" name="hall" required>
                                    <option value="">Select Hall</option>
                                    @foreach ($hall as $hall)
                                        <option value="{{ $hall->hall }}">{{ $hall->hall }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <select class="form-control" id="block" name="block" required>
                                    <option value="">Select Hall First</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <select class="form-control" id="room" name="room" required>
                                    <option value="">Select Block First</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <select class="form-control" id="bed" name="bed" required>
                                    <option value="">Select Room First</option>
                                </select>
                            </div>
                            <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Reserve">
                        </form>
                    @else
                        <div class="alert alert-info">
                            No Bed Space available
                        </div>
                    @endif
                @else
                    <div class="alert alert-info">
                        You need to have PIN before Applying.
                    </div>
                @endif
            </div>
            <div style="display: none;" id="wait">
                <div class="spinner-grow" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>

            <!-- Form End -->
        </div>
    </div>
    <!-- End Content-->
@endif
@if ($flag == 1)
    @foreach ($data as $row)
        @if ($row->hall != 'BOTs')
            <div class="card border-success mb-3 col-md-4">
                <div class="card-header bg-transparent border-success text-center">RESERVATION DETAILS <br>
                    <span class="card-text text-center">Ref ID:00{{ $row->id }}</span><br>
                    <span class="card-text text-center">ID No:{{ session('id_number') }}</span>
                </div>
                <div class="card-body text-info">
                    <h5 class="card-title text-center">{{ $row->hall }}</h5>
                    <p class="card-text">Block: <span style="float: right;">{{ $row->block }}</span></p>
                    <hr>
                    <p class="card-text">Room: <span style="float: right;">{{ $row->room }}</span></p>
                    <hr>
                    <p class="card-text">Bed: <span style="float: right;">{{ $row->bed }}</span></p>
                    <hr>
                </div>
                {{-- <div class="card-footer bg-transparent border-success text-center"><a href="/invoices/hostel"><i class="fas fa-money-check"></i> Generate Invoice</a></div> --}}
                @if ($row->payment_method == 'Online')
                    <div class="card-body text-center">
                        <form action="invoices/hostel" method="get" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="page" value="hostel">
                            <label>
                                <h5>Enter Your ACTIVE Email Address and Phone Number</h5>
                            </label>
                            <div class="input-group mb-3">
                                <span class="input-group-addon">
                                    <i class="fas fa-user f-30"></i>
                                </span>
                                <input id="email" type="email" class="form-control" name="email" required
                                    placeholder="Email Address">
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-addon">
                                    <i class="fas fa-phone f-30"></i>
                                </span>
                                <input id="phone" type="number" class="form-control" name="phone" required
                                    placeholder="Phone Number">
                            </div>
                            <input type="submit" id="submitButton" class="btn btn-primary shadow-2 mb-4" name="submit"
                                value="Generate Invoice">
                        </form>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h5>Goto Student Affairs</h5>
                    </div>
                @endif
                {{-- <div class="alert alert-info">
                    <h5>Unpaid hostel reservation will be revoke on 25 Dec 2025</h5>
                </div> --}}
            </div>
        @else
            <div class="alert alert-info">
                Report to the Office of the Dean of Students (Student Affairs).
            </div>
        @endif
    @endforeach
@endif
@if ($flag == 2)
    @foreach ($invoice as $row)
        <div class="card border-success mb-3 col-md-4">
            @foreach ($data as $rows)
                <div class="card-header bg-transparent border-success text-center">RESERVATION DETAILS <br>
                    <span class="card-text text-center">Ref ID:00{{ $rows->id }}</span><br>
                    <span class="card-text text-center">ID No:{{ session('id_number') }}</span>
                </div>
                <div class="card-body text-info">
                    <h5 class="card-title text-center">{{ $rows->hall }}</h5>
                    <p class="card-text">Block: <span style="float: right;">{{ $rows->block }}</span></p>
                    <hr>
                    <p class="card-text">Room: <span style="float: right;">{{ $rows->room }}</span></p>
                    <hr>
                    <p class="card-text">Bed: <span style="float: right;">{{ $rows->bed }}</span></p>
                    <hr>
                </div>
            @endforeach
            @if ($rows->hall != 'BOTs')
                <div class="card-header bg-transparent border-success text-center">PAYMENT INFOMATION <br>
                    <span class="card-text text-center">RRR:{{ $rrr }}</span>
                </div>
                <div class="card-body text-info">
                    <h5 class="card-title text-center">{{ $row->description }}</h5>
                    <p class="card-text">Name: <span style="float: right;">{{ $row->name }}</span></p>
                    <hr>
                    <p class="card-text">Phone: <span style="float: right;">{{ $row->phone }}</span></p>
                    <hr>
                    <p class="card-text">Email: <span style="float: right;">{{ $row->email }}</span></p>
                    <hr>
                </div>

                {{-- <div class="card-footer bg-transparent border-success text-center"><a href="/print invoice/{{ $rrr }}"><i class="fas fa-download"></i> Download</a></div> --}}

                <div class="card-footer bg-transparent border-success text-center">
                    <?php
                    $rrr = $rrr;
                    $merchantId = env('REMITA_MERCHANT_ID');
                    $apiKey = env('REMITA_API_KEY');
                    $hash = hash('sha512', $merchantId . '' . $rrr . '' . $apiKey);
                    ?>
                    <form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg" method="POST">
                        <input name="merchantId" value="{{ env('REMITA_MERCHANT_ID') }}" type="hidden">
                        <input name="hash" value="<?php echo $hash; ?>" type="hidden">
                        <input name="rrr" value="<?php echo $rrr; ?>" type="hidden">
                        <input name="responseurl" value="{{ env('REMITA_RESPONSE') }}response" type="hidden">
                        <input type="submit"value="Pay Now Via Remita" class="btn btn-danger btn-lg">
                    </form>
                </div>
                {{-- Add info to notify them that the hostel will be revoke if not pay on 31 nov 2025 --}}
                {{-- <div class="alert alert-info">
                    <h5>Unpaid hostel reservation will be revoke on 31 Nov 2025</h5>
                </div> --}}


                {{-- <div class="card-footer bg-transparent border-success text-center" onclick="window.print()"><i class="fas fa-print"></i> Print</div> --}}
            @else
                <div class="alert alert-info">
                    Report to the Office of the Dean of Students (Student Affairs).
                </div>
            @endif
        </div>
    @endforeach
@endif
@if ($flag == 3)
    @foreach ($data as $row)
        <div class="card border-success mb-3 col-md-4">
            <div class="card-header bg-transparent border-success text-center">RESERVATION DETAILS<br>
                <span class="card-text text-center">Ref ID:00{{ $row->id }}</span>
            </div>
            <div class="card-body text-info">
                <h5 class="card-title text-center">{{ $row->hall }}</h5>
                <p class="card-text">Block: <span style="float: right;">{{ $row->block }}</span></p>
                <hr>
                <p class="card-text">Room: <span style="float: right;">{{ $row->room }}</span></p>
                <hr>
                <p class="card-text">Bed: <span style="float: right;">{{ $row->bed }}</span></p>
                <hr>
            </div>
            <div class="card-footer bg-transparent border-success text-center"><a
                    href="/print-permit/{{ $row->id }}"><i class="fas fa-print"></i> Print Permit</a></div>

        </div>
    @endforeach
@endif
@if ($flag == 4)
    <div class="alert alert-info">
        <h4>Hostel Application</h4>

        <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
            <p>
                <strong>
                    Hostel Application Will be Open by 08:00PM - 27/01/2026.</strong>
            </p>
        </div>

        @if (DB::table('hostel_pin')->select('id', 'username')->where('username', session('id_number'))->first())
            <hr>
            <h4>PIN STATUS</h4>
            <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
                PIN Validated.
            </div>
        @else
            <hr>
            <h4>PIN STATUS</h4>
            <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
                You need to have PIN before Applying.
            </div>
        @endif
    </div>
@endif
@if ($flag == 5)
    <div class="alert alert-info">
        <h3>{{ $check['message'] }}</h3>
        <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
            @foreach ($check['reasons'] as $reason)
                <p>
                    - {{ $reason }}
                </p>
            @endforeach
        </div>
    </div>
@endif
@if (1)
    <div class="alert alert-info">
        <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
            {{-- <h3>
                All unpaid hostel reservations have been revoked.
            </h3> --}}
            {{-- Write hostel applying will be open tuesday 11, 6 am --}}
            <p>
                Please be informed that all unpaid hostel reservations will be revoked after one (1) week.
            </p>
            <p>
               Payment alone is not enough; if you do not verify and get your permit within one week, your allocation will be revoked, and you will not be attended to by Student Affairs.
            </p>
            {{-- <p>
                Thank you!
            </p>
            <p>
                From the Office of the Dean of Students.
            </p> --}}
        </div>
        <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
            <h3>
                To all students with hostel accommodation
            </h3>
            {{-- Write hostel applying will be open tuesday 11, 6 am --}}
            <p><strong>Selling hostel bed spaces is strictly prohibited</strong>
            </p>
            <p>
                Any student found selling or buying hostel permit will face severe disciplinary action, including
                expulsion. Hostel permits are non transferable and meant for personal use only.
                Report any suspicious activity to the hostel management or Dean of student.
                Stay compliant and avoid trouble.
            </p>
            <p>
                Professor Ahmadu Mohammed Brono
                <br>
                Dean Students’ Affairs
            </p>
        </div>

    </div>
@endif

{{-- Write message for student about time for opening hostel --}}
