@php
    use App\Models\HostelPin;
    use App\Models\User;
    use App\Models\Staff;
    use App\Models\Student;
    use App\Models\Alumni;
    use Illuminate\Support\Facades\DB;
    use App\Exports\PaidStudentsExport;
    use App\Exports\UnpaidStudentsExport;
    $fetching_session = \App\Http\Controllers\SystemSettingsController::getHostelFeesSession();
    $hostelFeeStudents = DB::table('invoices')
        ->where(['status' => 'Paid', 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => $fetching_session])
        ->select('username')
        ->distinct()
        ->count('username');
    $schoolFeesSession = \App\Http\Controllers\SystemSettingsController::getSchoolFeesSession();
    $schoolFeeTotals = PaidStudentsExport::paidTotals($schoolFeesSession);
    $schoolFeeStudents = (int) ($schoolFeeTotals['students'] ?? 0);
    $unpaidStudentCount = (int) UnpaidStudentsExport::summaryTotals($schoolFeesSession)['students'];
    $pin = HostelPin::where(['flag' => 1])
        ->where('username', '!=', 'Awaiting')
        ->count();
    $student = User::where(['accType' => 'Student'])->count();
    $student = Student::where(['school_fee' => '1', 'status' => '1'])
        ->select('school_fee')
        ->count();
    $staff = Staff::count();
    $alumni = Alumni::count();
@endphp
<div class="row">
    @forelse (DB::table('rolls')->where(['username' => session('username'), 'page' => 'Dashboard'])->select('action')->limit(1)->get() as $item)
        @php
            $actions = explode(',', $item->action);
        @endphp
        @forelse ($actions as $item)
            @if ($item == 'active_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Active Student</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($student) }}</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'active_staff')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Active Staff</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($staff) }}</h3>
                            <i class="fas fa-user-tag f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students Who Paid School Fees</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($schoolFeeStudents) }}</h3>
                            <i class="fas fa-money-bill-wave f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'unpaid_student')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students With Unpaid Fees</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($unpaidStudentCount) }}</h3>
                            <i class="fas fa-money-bill-wave f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_hostel_pin')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students Who Paid Hostel PIN</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($pin) }}</h3>
                            <i class="fas fa-bed f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_hostel')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students Who Paid Hostel Fees</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($hostelFeeStudents) }}</h3>
                            <i class="fas fa-bed f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_certificate')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students Who Paid Certificate Fees</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-certificate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'paid_transcript')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card theme-bg bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Students Who Paid Transcript Fees</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-address-card f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'suspended')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Suspended Students</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'nysc')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Pre-Mobilize Students</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'exemption')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Exemption Student</h5>
                            <h3 class="text-white mb-2 f-w-300">0</h3>
                            <i class="fas fa-user-graduate f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
            @if ($item == 'alumni')
                <div class="col-sm-6 col-md-6 col-xl-3">
                    <div class="card bg-c-blue bitcoin-wallet">
                        <div class="card-block">
                            <h5 class="text-white mb-2">Alumni</h5>
                            <h3 class="text-white mb-2 f-w-300">{{ number_format($alumni) }}</h3>
                            <i class="fas fa-graduation-cap f-70 text-white"></i>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- [ Main Content ] start -->
                    <div class="row">
                        <!-- [ bitcoin-wallet section ] start-->
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="card bg-c-blue bitcoin-wallet">
                                <div class="card-block">
                                    <h5 class="text-white mb-2">DASHBOARD</h5>
                                    <h3 class="text-white mb-2 f-w-300"></h3>
                                    <i class="fas fa-home f-70 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            </div>
        @endforelse
    @empty
        <div class="main-body">
            <div class="page-wrapper">
                <!-- [ Main Content ] start -->
                <div class="row">
                    <!-- [ bitcoin-wallet section ] start-->
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <div class="card bg-c-blue bitcoin-wallet">
                            <div class="card-block">
                                <h5 class="text-white mb-2">DASHBOARD</h5>
                                <h3 class="text-white mb-2 f-w-300"></h3>
                                <i class="fas fa-home f-70 text-white"></i>
                            </div>
                        </div>
                    </div>
                    <!-- [ Main Content ] end -->
                </div>
            </div>
        </div>
    @endforelse

    @forelse (DB::table('committee_membership')->where(['username' => session('username')])->get() as $item1)
        <div class="main-body">
            <div class="page-wrapper">
                <!-- [ Main Content ] start -->
                <div class="row">
                    @forelse (DB::table('committee_meetings')->where(['committee' => $item1 -> committee, 'sub_committee' => $item1 -> sub_committee])->orderBy('start_at', 'asc')->get() as $item2)
                        <div class="col-sm-12 col-lg-4">
                            <a href="/committee-meeting-members/{{ $item2->id }}">
                                <div class="card">
                                    <div class="card-body row">
                                        <div class="card-block col-md-12">
                                            <p>{{ $item2->sub_committee }}</p>
                                            <p>
                                                {{ date('M d, Y', strtotime($item2->start_at)) }} -
                                                {{ date('M d, Y', strtotime($item2->end_at)) }}
                                            </p>
                                            <p>
                                                {{ date('h:i A', strtotime($item2->start_at)) }} -
                                                {{ date('h:i A', strtotime($item2->end_at)) }}
                                            </p>
                                            <p><i class="fa fa-map-marker"></i> Board Room</p>
                                        </div>
                                    </div>
                                </div>
                            </a>

                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>


    @empty

    @endforelse


</div>
