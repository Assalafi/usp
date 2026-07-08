@php
    use Illuminate\Support\Facades\DB;
    $datas = DB::table('students')
        ->where('user_id', session('id'))
        ->select('faculty', 'department', 'program', 'level_of_entry', 'session_of_entry', 'jamb_no')
        ->get();
    $x = 0;

    $paymentSession = [];
    $paymentData = [];
    $sessionAmountToPay = [];
    $sessionAmountPaid = [];
    $amountReturn = 0;
    $amount = 0;
    $amountPaid = 0;
    $paymentStatus = 'no';
    $schoolStatus = 'yes';
    foreach ($datas as $row) {
        $x = 1;
    }
    if ($x == 0) {
        die();
    }
    $invs = DB::table('invoices')
        ->where([
            'username' => session('id'),
            'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES',
            'status' => 'Pending',
        ])
        ->limit(1)
        ->orderBy('id', 'ASC')
        ->get();
    $id_card_invs = DB::table('invoices')
        ->where([
            'username' => session('id'),
            'description' => 'ID CARDS',
            'status' => 'Pending',
        ])
        ->limit(1)
        ->orderBy('id', 'ASC')
        ->get();
    if (session('system_session') == session('student_session')) {
        $amountPaid = $sessionAmountPaid[session('system_session')] = DB::table('invoices')
            ->where([
                'username' => session('id'),
                'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES',
                'status' => 'Paid',
            ])
            ->orderBy('id', 'ASC')
            ->sum('amount');
        $amount = $sessionAmountToPay[session('system_session')] = DB::table('school_fees')
            ->where(['program' => $row->program, 'level' => $row->level_of_entry, 'type' => 'NEW'])
            ->select('amount')
            ->value('amount');

        $paymentSession['session'] = session('system_session');
        $paymentSession['amount'] = $sessionAmountToPay[session('system_session')];
        $paymentSession['paid'] = $sessionAmountPaid[session('system_session')];
        $paymentData[] = $paymentSession;

        if ($paymentSession['session'] == session('system_session') && $paymentSession['paid'] > 0) {
            $paymentStatus = 'yes';
        }
    } else {
        $returnData = DB::table('session_history')
            ->where(['username' => session('id_number')])
            ->get();
        $ss = 0;
        foreach ($returnData as $return) {
            // if (
            //     $return->session == session('system_session') &&
            //     (strtoupper($return->status) == 'PROCEED' || strtoupper($return->status) == 'REPEAT')
            // ) {
            //     $ss = 1;
            // }

            if (
                strtoupper($return->status) == 'PROCEED' ||
                strtoupper($return->status) == 'REPEAT' ||
                strtoupper($return->status) == 'PENDING'
            ) {
                $ss = 1;
            }
            if ($return->session == $row->session_of_entry) {
                $amount += $sessionAmountToPay[$return->session] = DB::table('school_fees')
                    ->where(['program' => $row->program, 'level' => $return->level, 'type' => 'NEW'])
                    ->select('amount')
                    ->value('amount');
            } else {
                $amount += $sessionAmountToPay[$return->session] = DB::table('school_fees')
                    ->where(['program' => $row->program, 'level' => $return->level, 'type' => 'RETURNING'])
                    ->select('amount')
                    ->value('amount');
            }

            $amountPaid += $sessionAmountPaid[$return->session] = DB::table('invoices')
                ->where([
                    'username' => session('id'),
                    'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES',
                    'status' => 'Paid',
                    'session' => $return->session,
                ])
                ->orderBy('id', 'ASC')
                ->sum('amount');

            // store session,amount, and amount paid in an multi array
            $paymentSession['session'] = $return->session;
            $paymentSession['amount'] = $sessionAmountToPay[$return->session];
            $paymentSession['paid'] = $sessionAmountPaid[$return->session];
            $paymentData[] = $paymentSession;

            if ($paymentSession['session'] == session('system_session') && $paymentSession['paid'] > 0) {
                $paymentStatus = 'yes';
            }
            // how to loop through the array
        }
        if ($ss == 0) {
            $schoolStatus = 'no';
        }
    }

    $remain = $amount - $amountPaid;
    if ($remain < 0) {
        $remain = 0;
    }
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}</h5>
                    </div>
                    <div class="card-block">

                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyInvoice"><i
                                class="fas fa-money-check"></i> {{ 'Verify Invoice' }}</a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolFees"><i
                                class="fas fa-school"></i> {{ 'School Fees' }}</a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#idCard"><i
                                class="fas fa-id-card"></i> {{ 'ID Card Payment' }}</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <style>
                            @media screen and (max-width: 768px) {
                                #export-table thead {
                                    display: none;
                                }

                                #export-table,
                                #export-table tbody,
                                #export-table tr,
                                #export-table td {
                                    display: block;
                                    width: 100%;
                                }

                                #export-table tr {
                                    background-color: #ffffff;
                                    margin-bottom: 0.75rem;
                                    /* Reduced spacing */
                                    border: 1px solid #e0e0e0;
                                    border-radius: 6px;
                                    padding: 0.75rem;
                                    /* Reduced padding */
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
                                }

                                #export-table td {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    text-align: right;
                                    padding: 0.6rem 0.25rem;
                                    /* Reduced padding */
                                    border-bottom: 1px solid #f0f0f0;
                                    font-size: 0.8rem;
                                    /* Reduced font size */
                                }

                                #export-table td:last-child {
                                    border-bottom: none;
                                }

                                #export-table td::before {
                                    content: attr(data-label);
                                    font-weight: 600;
                                    text-align: left;
                                    padding-right: 1rem;
                                    color: #555;
                                }

                                .status-badge {
                                    padding: 0.2rem 0.5rem;
                                    border-radius: 10px;
                                    font-size: 0.7rem;
                                    /* Reduced font size */
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    letter-spacing: 0.5px;
                                }

                                .status-badge.status-pending {
                                    background-color: #fff0c2;
                                    color: #a67c00;
                                }

                                .status-badge.status-paid {
                                    background-color: #c8e6c9;
                                    color: #256029;
                                }
                            }
                        </style>
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'Description' }}</th>
                                        <th>{{ 'Amount' }}</th>
                                        <th>{{ 'Sponsor' }}</th>
                                        <th>{{ 'RRR' }}</th>
                                        <th>{{ 'Session' }}</th>
                                        <th>{{ 'Status' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $rows)
                                        <tr>
                                            <td data-label="#">{{ $sn++ }}</td>
                                            <td data-label="Description">{{ $rows->description }}</td>
                                            <td data-label="Amount">NGN {{ number_format($rows->amount, 2) }}</td>
                                            <td data-label="Sponsor">
                                                {{ $rows->fees_type == 'nelfund' ? 'NELFUND' : 'Self Sponsor' }}</td>
                                            <td data-label="RRR">{{ $rows->rrr }}</td>
                                            <td data-label="Session">{{ $rows->session }}</td>
                                            <td data-label="Status"><span
                                                    class="status-badge status-{{ strtolower($rows->status) }}">{{ $rows->status }}</span>
                                            </td>
                                            <td data-label="Action">
                                                @if ($rows->status == 'Pending')
                                                    <button type="button" class="btn btn-icon btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete{{ $rows->id }}">
                                                        <i class="fas fa-credit-card"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('print.receipt', $rows->rrr ?? '12345678') }}"
                                                        class="btn btn-icon btn-info btn-sm">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="delete{{ $rows->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">PAYMENT</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    {{-- <div class="card-footer bg-transparent border-success text-center"><a href="/print invoice/{{ $row -> rrr }}"><i class="fas fa-download"></i> Print Invoice</a>
                                                </div> --}}
                                                    <div class="card text-center">
                                                        <?php
                                                        $rrr = $rows->rrr;
                                                        $merchantId = env('REMITA_MERCHANT_ID');
                                                        $apiKey = env('REMITA_API_KEY');
                                                        $hash = hash('sha512', $merchantId . '' . $rrr . '' . $apiKey);
                                                        ?>
                                                        <form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg"
                                                            method="POST">
                                                            <input name="merchantId"
                                                                value="{{ env('REMITA_MERCHANT_ID') }}" type="hidden">
                                                            <input name="hash" value="<?php echo $hash; ?>"
                                                                type="hidden">
                                                            <input name="rrr" value="<?php echo $rrr; ?>"
                                                                type="hidden">
                                                            <input name="responseurl"
                                                                value="{{ env('REMITA_RESPONSE') }}response"
                                                                type="hidden">
                                                            <input type="submit"value="Pay Now Via Remita"
                                                                class="btn btn-danger btn-lg">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<!-- Show modal content -->
<div id="verifyInvoice" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Verify Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group needs-validation" novalidate action="verify" method="GET"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="rrr"></label>
                            <input type="text" name="rrr" id="rrr" placeholder="Enter RRR"
                                class="form-control" required>
                            <div class="invalid-feedback"> You must enter RRR number </div>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Verify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="schoolFees" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <!-- Start Content-->
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="mb-2">Schools Fees</h4>
                    <!-- Form Start -->
                    <div class="card-body">
                        {{-- <p>COURSE: {{ DB::table('program')->where('code', $row->program)->value('title') }}</p> --}}
                        {{-- session amount --}}
                        @foreach ($paymentData as $item)
                            <h6>{{ $item['session'] }} Academic Session</h6>
                            <div
                                style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
                                <p>Amount: N{{ number_format($item['amount'], 2) }}</p>
                                <p>Amount Paid: N{{ number_format($item['paid'], 2) }}</p>
                            </div>
                        @endforeach

                        <h6>Payment Summary</h6>
                        <div
                            style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
                            <p>Overall Amount: N{{ number_format($amount, 2) }}</p>
                            <p>Total Paid: N{{ number_format($amountPaid, 2) }}</p>
                            <p><strong>Remaining Fees:</strong> N{{ number_format($remain, 2) }}</p>
                        </div>
                    </div>
                    @forelse ($invs as $inv)
                        <div class="card-footer bg-transparent border-success text-center">
                            <?php
                            $rrr = $inv->rrr;
                            $merchantId = env('REMITA_MERCHANT_ID');
                            $apiKey = env('REMITA_API_KEY');
                            $hash = hash('sha512', $merchantId . '' . $rrr . '' . $apiKey);
                            ?>
                            <form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg" method="POST">
                                <input name="merchantId" value="{{ env('REMITA_MERCHANT_ID') }}" type="hidden">
                                <input name="hash" value="<?php echo $hash; ?>" type="hidden">
                                <input name="rrr" value="<?php echo $rrr; ?>" type="hidden">
                                <input name="responseurl" value="{{ env('REMITA_RESPONSE') }}response"
                                    type="hidden">
                                <input type="submit" value="Pay Now Via Remita" class="btn btn-danger btn-lg">
                            </form>
                        </div>
                    @empty
                        @if ($remain > 0 && $schoolStatus == 'yes')
                            <form action="invoices/school-fees" method="get" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="page" value="school-fees">
                                <input type="hidden" name="try" value="second">
                                {{-- <div class="input-group mb-3">
                                    <span class="input-group-addon">
                                        <i class="fas fa-money-check f-40"></i>
                                    </span>
                                </div> --}}

                                <label>Make Payment</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-addon">
                                        <i class="fas fa-money-check f-40"></i>
                                    </span>
                                    @if ($paymentStatus == 'yes')
                                        <select name="amount" class="form-control" id="p2" required>
                                            <option value="">Select Amount*</option>
                                            <option value="{{ $remain }}">{{ 'N' . number_format($remain, 2) }}
                                            </option>
                                        </select>
                                    @else
                                        <select name="amount" class="form-control" id="p2" required>
                                            <option value="">Select Amount*</option>
                                            <option value="{{ $remain }}">Full Payment (100%)
                                                {{ 'N' . number_format($remain, 2) }}</option>
                                            <option value="{{ $remain * 0.5 }}">Half Payment (50%)
                                                {{ 'N' . number_format($remain * 0.5, 2) }}</option>
                                        </select>
                                    @endif

                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-addon">
                                        <i class="fas fa-user f-40"></i>
                                    </span>
                                    <input id="email" type="email" class="form-control" name="email"
                                        required placeholder="Enter Your Active Email">
                                </div>
                                <input type="submit" id="submitButton" class="btn btn-primary shadow-2 mb-4"
                                    name="submit" value="Generate Invoice">
                            </form>
                        @else
                            <div
                                style="padding: 10px; background-color: #f49e9e; border-left: 4px solid #ff0000; margin: 20px 0;">
                                @if ($schoolStatus == 'no')
                                    <div class="text-center">
                                        <h5>Your Academic Status is Pending.</h5>
                                    </div>
                                @endif
                                <div class="text-center">
                                    <h5>Nothing to pay for now. Try Later</h5>
                                </div>

                            </div>
                        @endif
                    @endforelse
                    <!-- Form End -->
                </div>
            </div>
            <!-- End Content-->
        </div>
    </div>
</div>

<div id="idCard" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">ID Card Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <!-- Start Content-->
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="mb-2">ID Card Payment</h4>
                    {{-- Info for the amount N4,000 --}}
                    <p class="mb-2">Amount: N2,000</p>
                    <!-- Form Start -->
                    <div class="card-body">
                        {{-- <p>COURSE: {{ DB::table('program')->where('code', $row->program)->value('title') }}</p> --}}
                        {{-- session amount --}}

                        @forelse ($id_card_invs as $inv)
                            <div class="card-footer bg-transparent border-success text-center">
                                <?php
                                $rrr = $inv->rrr;
                                $merchantId = env('REMITA_MERCHANT_ID');
                                $apiKey = env('REMITA_API_KEY');
                                $hash = hash('sha512', $merchantId . '' . $rrr . '' . $apiKey);
                                ?>
                                <form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg" method="POST">
                                    <input name="merchantId" value="{{ env('REMITA_MERCHANT_ID') }}" type="hidden">
                                    <input name="hash" value="<?php echo $hash; ?>" type="hidden">
                                    <input name="rrr" value="<?php echo $rrr; ?>" type="hidden">
                                    <input name="responseurl" value="{{ env('REMITA_RESPONSE') }}response"
                                        type="hidden">
                                    <input type="submit" value="Pay Now Via Remita" class="btn btn-danger btn-lg">
                                </form>
                            </div>
                        @empty

                            <form action="invoices/school-fees" method="get" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="page" value="id-card">
                                <input type="hidden" name="try" value="second">
                                {{-- phone --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-addon">
                                        <i class="fas fa-phone f-40"></i>
                                    </span>
                                    <input id="phone" type="text" class="form-control" name="phone"
                                        required placeholder="Enter Your Active Phone Number">
                                </div>
                                {{-- email --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-addon">
                                        <i class="fas fa-envelope f-40"></i>
                                    </span>
                                    <input id="email" type="email" class="form-control" name="email"
                                        required placeholder="Enter Your Active Email">
                                </div>
                                <input type="submit" id="submitButton" class="btn btn-primary shadow-2 mb-4"
                                    name="submit" value="Generate Invoice">
                            </form>
                        @endforelse
                        <!-- Form End -->
                    </div>
                </div>
                <!-- End Content-->
            </div>
        </div>
    </div>
