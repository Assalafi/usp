<style>
.coc-stat{border-radius:10px;border:none;box-shadow:0 1px 8px rgba(0,0,0,.08)}
.coc-stat .card-body{padding:15px 18px}
.coc-stat .s-icon{width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;margin-right:12px;flex-shrink:0}
.coc-stat h3{margin:0;font-size:22px;font-weight:700;line-height:1}
.coc-stat small{color:#6c757d;font-size:12px}
.coc-wrap{max-width:170px;white-space:normal;word-wrap:break-word}
.coc-from{background:#fef3cd;color:#856404;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4}
.coc-to{background:#d1ecf1;color:#0c5460;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4;font-weight:600}
.coc-appno{font-family:monospace;font-weight:700;color:#0d6efd;font-size:12px}
.coc-date{color:#888;font-size:10px}
.coc-name{font-weight:600;color:#212529;font-size:13px}
.coc-id{color:#888;font-size:11px}
.coc-table{table-layout:fixed}
.coc-table thead th{background:#343a40!important;color:#fff!important;font-size:12px;font-weight:600;padding:10px 12px;white-space:nowrap;border:none}
.coc-table thead th:nth-child(1){width:40px}
.coc-table thead th:nth-child(2){width:140px}
.coc-table thead th:nth-child(3){width:180px}
.coc-table thead th:nth-child(4){width:180px}
.coc-table thead th:nth-child(5){width:180px}
.coc-table thead th:nth-child(6){width:160px}
.coc-table thead th:nth-child(7){width:120px}
.coc-table tbody td{padding:10px 12px;vertical-align:middle;font-size:13px;border-top:1px solid #eee;word-wrap:break-word;overflow:hidden}
.coc-table tbody tr:hover{background:#f0f4ff}
.bg-purple{background:#6f42c1!important}
</style>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Change of Course Applications</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#!">Change of Course</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        @php
            $statusCounts = [
                'processing' => DB::table('change_of_course')->whereNotIn('status',['Payment Pending','Approved','Rejected'])->count(),
                'approved' => DB::table('change_of_course')->where('status','Approved')->count(),
                'rejected' => DB::table('change_of_course')->where('status','Rejected')->count(),
            ];
        @endphp
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#0d6efd"><i class="fas fa-file-alt"></i></div>
                        <div><h3>{{ $applications->total() }}</h3><small>Total</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#17a2b8"><i class="fas fa-hourglass-half"></i></div>
                        <div><h3>{{ $statusCounts['processing'] }}</h3><small>In Progress</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#28a745"><i class="fas fa-check-circle"></i></div>
                        <div><h3>{{ $statusCounts['approved'] }}</h3><small>Approved</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#dc3545"><i class="fas fa-times-circle"></i></div>
                        <div><h3>{{ $statusCounts['rejected'] }}</h3><small>Rejected</small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter mr-2"></i> Filter Applications</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('change-of-course.admin') }}">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2">
                                    <label class="mb-1"><strong>Status</strong></label>
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="Payment Pending" {{ request('status') == 'Payment Pending' ? 'selected' : '' }}>Payment Pending</option>
                                        <option value="Awaiting New HOD" {{ request('status') == 'Awaiting New HOD' ? 'selected' : '' }}>Awaiting New HOD</option>
                                        <option value="Awaiting New Dean" {{ request('status') == 'Awaiting New Dean' ? 'selected' : '' }}>Awaiting New Dean</option>
                                        <option value="Awaiting Provost" {{ request('status') == 'Awaiting Provost' ? 'selected' : '' }}>Awaiting Provost</option>
                                        <option value="Awaiting Current HOD" {{ request('status') == 'Awaiting Current HOD' ? 'selected' : '' }}>Awaiting Current HOD</option>
                                        <option value="Awaiting Current Dean" {{ request('status') == 'Awaiting Current Dean' ? 'selected' : '' }}>Awaiting Current Dean</option>
                                        <option value="Awaiting Registrar" {{ request('status') == 'Awaiting Registrar' ? 'selected' : '' }}>Awaiting Registrar</option>
                                        <option value="Awaiting VC" {{ request('status') == 'Awaiting VC' ? 'selected' : '' }}>Awaiting VC</option>
                                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="mb-1"><strong>Session</strong></label>
                                    <select name="session" class="form-control">
                                        <option value="">All Sessions</option>
                                        @php
                                            $currentYear = (int) date('Y');
                                            for ($year = $currentYear; $year >= 2020; $year--) {
                                                $nextYear = $year + 1;
                                                $sessionValue = $year . '/' . $nextYear;
                                                echo '<option value="' . $sessionValue . '"' . (request('session') == $sessionValue ? ' selected' : '') . '>' . $sessionValue . '</option>';
                                            }
                                        @endphp
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-primary btn-block w-100">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <a href="{{ route('change-of-course.admin') }}" class="btn btn-outline-secondary btn-block w-100">
                                        <i class="fas fa-redo mr-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Applications List</h5>
                        <span class="badge badge-primary p-2" style="font-size:13px">{{ $applications->total() }} Total</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover coc-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>App No</th>
                                        <th>Student</th>
                                        <th>From Dept</th>
                                        <th>To Dept</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $index => $app)
                                        @php
                                            $fromDept = DB::table('department')->where('code', $app->current_department)->value('title');
                                            $toDept = DB::table('department')->where('code', $app->new_department)->value('title');
                                        @endphp
                                        <tr>
                                            <td>{{ $applications->firstItem() + $index }}</td>
                                            <td>
    <span class="coc-appno">{{ $app->application_no }}</span><br>
    <span class="coc-date">{{ date('d M Y', strtotime($app->created_at)) }}</span>
</td>
                                            <td>
                                                <span class="coc-name">{{ $app->student_name }}</span><br>
                                                <span class="coc-id">{{ $app->username }}</span>
                                            </td>
                                            <td class="coc-wrap"><span class="coc-from">{{ $fromDept }}</span></td>
                                            <td class="coc-wrap"><span class="coc-to">{{ $toDept }}</span></td>
                                            <td>
                                                @php
                                                    $sc = ['Approved'=>'success','Rejected'=>'danger','Payment Pending'=>'warning','Awaiting Provost'=>'purple','Awaiting VC'=>'dark'];
                                                    $color = $sc[$app->status] ?? 'info';
                                                @endphp
                                                <span class="status-badge bg-{{ $color }} {{ in_array($color, ['warning']) ? 'text-dark' : 'text-white' }}">{{ $app->status }}</span>
                                            </td>
                                            <td style="white-space:nowrap">
                                                <a href="{{ route('change-of-course.show', $app->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if(session('accType') == 'Admin')
                                                <a href="{{ route('change-of-course.bulk-edit', $app->id) }}" class="btn btn-sm btn-warning" title="Bulk Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center p-5">
                                                <i class="fas fa-inbox" style="font-size:36px;color:#ccc"></i>
                                                <p class="text-muted mt-2 mb-0">No applications found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination and Results Info -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }}
                                        of {{ $applications->total() ?? 0 }} results
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $applications->appends(request()->query())->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
