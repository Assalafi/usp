<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>FEES COLLECTION</h5>
                        <a href="/admin/receipts" class="btn btn-success float-end me-2">
                            <i class="fas fa-file-pdf"></i> Receipts
                        </a>
                        <button type="button" class="btn btn-success float-end me-2" data-bs-toggle="modal" data-bs-target="#exportPaidStudentsModal">
                            <i class="fas fa-file-excel"></i> Export Paid Students
                        </button>
                        <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                            data-bs-target="#bulkVerifyModal">
                            <i class="fas fa-check-circle"></i> Bulk Verify RRR
                        </button>

                    </div>
                    <div class="card-block row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table id="" class="display table nowrap table-striped table-hover"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ 'DESCRIPTION' }}</th>
                                            <th>{{ 'TOTAL AMOUNT PAID' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ 1 }}</td>
                                            <td>HOSTEL-MAINTENANCE/FEES</td>
                                            <td>N{{ number_format($hostel, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ 2 }}</td>
                                            <td>SCHOOL FEES</td>
                                            <td>N{{ number_format($school, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ 2 }}</td>
                                            <td>TOTAL</td>
                                            <td>N{{ number_format($school + $hostel, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form class="needs-validation" novalidate method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-3">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            <option value="{{ $_GET['faculty'] ?? '' }}">
                                                {{ $_GET['faculty'] ?? 'Select Option' }}</option>
                                            @foreach ($faculty as $roww)
                                                <option value="{{ $roww->code }}">{{ $roww->title }}
                                                    ({{ $roww->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"> You must select FACULTY </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="departmentf">Department</label>
                                        <select class="form-control department" lang="f" id="departmentf"
                                            name="department">
                                            <option value="{{ $_GET['department'] ?? '' }}">
                                                {{ $_GET['department'] ?? 'Select Faculty First' }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="programf">Program</label>
                                        <select class="form-control" id="programf" lang="f" name="program">
                                            <option value="{{ $_GET['program'] ?? '' }}">
                                                {{ $_GET['program'] ?? 'Select Department First' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="description">Description</label>
                                        <select class="form-control" id="description" name="description">
                                            <option value="{{ $_GET['description'] ?? '' }}">
                                                {{ $_GET['description'] ?? 'Select Option' }}</option>
                                            <option value="UNIVERSITY OF MAIDUGURI-1000127 FEES">UNIVERSITY OF
                                                MAIDUGURI-1000127 FEES</option>
                                            <option value="HOSTEL-MAINTENANCE/FEES">HOSTEL-MAINTENANCE/FEES</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="rrr">RRR</label>
                                        <input type="text" class="form-control" id="rrr" name="rrr"
                                            placeholder="Enter RRR" value="{{ $_GET['rrr'] ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="{{ $_GET['status'] ?? '' }}">
                                                {{ $_GET['status'] ?? 'Select Option' }}</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="status">Start At</label>
                                        <input type="date" name="start" id="start" class="form-control"
                                            placeholder="Start At" value="{{ $_GET['start'] ?? '2024-01-01' }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="status">End At</label>
                                        <input type="date" name="end" id="end" class="form-control"
                                            placeholder="End At" value="{{ $_GET['end'] ?? date('Y-m-d') }}">
                                    </div>
                                    {{-- session --}}
                                    <div class="col-md-2">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session">
                                            <option value="{{ $_GET['session'] ?? session('system_session') }}">
                                                {{ $_GET['session'] ?? 'Select Option' }}</option>

                                            <option value="2028/2029">2028/2029</option>
                                            <option value="2027/2028">2027/2028</option>
                                            <option value="2026/2027">2026/2027</option>
                                            <option value="2025/2026">2025/2026</option>
                                            <option value="2024/2025">2024/2025</option>
                                            <option value="2023/2024">2023/2024</option>
                                            <option value="2022/2023">2022/2023</option>
                                            <option value="2021/2022">2021/2022</option>
                                            <option value="2020/2021">2020/2021</option>
                                            <option value="2019/2020">2019/2020</option>
                                            <option value="2018/2019">2018/2019</option>
                                            <option value="2017/2018">2017/2018</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <button type="submit" style="width: 100%"
                                                class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                                {{ 'Filter' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'NAME' }}</th>
                                        <th>{{ 'AMOUNT' }}</th>
                                        <th>{{ 'RRR' }}</th>
                                        <th>{{ 'DESCRIPTION' }}</th>
                                        <th>{{ 'PHONE' }}</th>
                                        <th>{{ 'STATUS' }}</th>
                                        <th>{{ 'DATE' }}</th>
                                        <th>{{ 'ACTION' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>N{{ number_format($row->amount, 2) }}</td>
                                            <td>{{ $row->rrr }}</td>
                                            <td>{{ $row->description }}</td>
                                            <td>{{ $row->phone }}</td>
                                            <td>{{ $row->status }}</td>
                                            <td>{{ date('d-m-y', strtotime($row->updated_at)) }}</td>
                                            <td>

                                                <button type="button"
                                                    class="btn btn-icon btn-primary btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSessionModal{{ $row->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="editSessionModal{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="editSessionModalLabel{{ $row->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editSessionModalLabel{{ $row->id }}">Update Session</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="update {{ $page }}" method="POST">
                                                        <div class="modal-body">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="session{{ $row->id }}">Session</label>
                                                                <select class="form-control" id="session{{ $row->id }}" name="session" required>
                                                                    <option value="">Select Session</option>
                                                                    <option value="2028/2029" {{ $row->session == '2028/2029' ? 'selected' : '' }}>2028/2029</option>
                                                                    <option value="2027/2028" {{ $row->session == '2027/2028' ? 'selected' : '' }}>2027/2028</option>
                                                                    <option value="2026/2027" {{ $row->session == '2026/2027' ? 'selected' : '' }}>2026/2027</option>
                                                                    <option value="2025/2026" {{ $row->session == '2025/2026' ? 'selected' : '' }}>2025/2026</option>
                                                                    <option value="2024/2025" {{ $row->session == '2024/2025' ? 'selected' : '' }}>2024/2025</option>
                                                                    <option value="2023/2024" {{ $row->session == '2023/2024' ? 'selected' : '' }}>2023/2024</option>
                                                                    <option value="2022/2023" {{ $row->session == '2022/2023' ? 'selected' : '' }}>2022/2023</option>
                                                                    <option value="2021/2022" {{ $row->session == '2021/2022' ? 'selected' : '' }}>2021/2022</option>
                                                                    <option value="2020/2021" {{ $row->session == '2020/2021' ? 'selected' : '' }}>2020/2021</option>
                                                                    <option value="2019/2020" {{ $row->session == '2019/2020' ? 'selected' : '' }}>2019/2020</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure</h4>
                                                        </div>
                                                        <form class="form-group" action="delete {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
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

<!-- Offline Payment Creation Section -->
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-money-check-alt me-2"></i>Create Offline Payment</h5>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#offlinePaymentSection">
                <i class="fas fa-chevron-down"></i> Toggle
            </button>
        </div>
        <div class="collapse show" id="offlinePaymentSection">
            <div class="card-block">
                <p class="text-muted mb-3">For students/applicants who already paid offline (cash, bank transfer). Creating a payment here allows them to skip the online payment step.</p>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" id="offlineSearch" class="form-control" placeholder="Search by name, username, ID number or email...">
                            <button class="btn btn-primary" id="offlineSearchBtn" type="button">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <span id="offlineResultCount" class="badge bg-secondary mt-2 d-inline-block"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="offlinePaymentTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username / Email</th>
                                <th>Account Type</th>
                                <th>Faculty</th>
                                <th>Department</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="offlinePaymentBody">
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search me-2"></i>Search for a student or transfer applicant above</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Bulk Verify RRR Modal -->
<div id="bulkVerifyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Verify RRR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <p>Select a service type to verify all RRRs for that service. This will check payment status for all
                        invoices and update their status accordingly (Paid, Pending, Invalid, Unpaid).</p>
                    <form id="bulkVerifyForm" action="{{ route('admin.bulk_verify_progress') }}" method="GET">
                        <div class="form-group">
                            <label for="serviceTypeSelect">Service Type</label>
                            <select name="serviceTypeId" id="serviceTypeSelect" class="form-control" required>
                                <option value="">-- Select Service Type --</option>
                                @php
                                    $serviceTypes = DB::table('invoices')
                                        ->select('serviceTypeId', 'description')
                                        ->whereNotNull('rrr')
                                        ->distinct()
                                        ->get();
                                @endphp
                                @foreach ($serviceTypes as $serviceType)
                                    <option value="{{ $serviceType->serviceTypeId }}">
                                        {{ $serviceType->description }} ({{ $serviceType->serviceTypeId }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Process Verification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Show modal content -->
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control">
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Create Offline Payment Modal -->
<div id="createOfflinePaymentModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Offline Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createOfflineForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong id="modalUserName"></strong><br>
                        <small id="modalUserInfo" class="text-muted"></small>
                    </div>
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                        <select name="fee_type" id="modalFeeType" class="form-control" required>
                            <option value="">-- Select Fee Type --</option>
                            <optgroup label="Change of Course (Departmental)" id="cocOptions">
                                <option value="coc_voluntary">Voluntary Transfer</option>
                                <option value="coc_obligatory">Obligatory Transfer</option>
                            </optgroup>
                            <optgroup label="Inter-University Transfer" id="iutOptions">
                                <option value="iut_nigeria">Within Nigeria</option>
                                <option value="iut_abroad">From Abroad</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₦)</label>
                        <input type="text" id="modalAmountDisplay" class="form-control" readonly style="background:#f0f0f0; font-weight:bold; font-size:18px;">
                        <small class="text-muted">Amount is set automatically based on fee type from system settings</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference / Teller No <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" id="modalReference" class="form-control" required placeholder="Bank teller number or receipt reference">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Receipt (Optional)</label>
                        <input type="file" name="payment_receipt" id="modalReceipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">JPG, PNG or PDF, max 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="createPaymentBtn">
                        <i class="fas fa-check me-1"></i> Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var feeAmounts = {
        coc_voluntary: {{ \App\Http\Controllers\SystemSettingsController::get('change_of_course_fee_voluntary', 100000) }},
        coc_obligatory: {{ \App\Http\Controllers\SystemSettingsController::get('change_of_course_fee_obligatory', 50000) }},
        iut_nigeria: {{ \App\Http\Controllers\SystemSettingsController::get('inter_university_transfer_fee_nigeria', 150000) }},
        iut_abroad: {{ \App\Http\Controllers\SystemSettingsController::get('inter_university_transfer_fee_abroad', 250000) }}
    };

    function searchUsers() {
        var q = $('#offlineSearch').val().trim();
        if (q.length < 2) { alert('Enter at least 2 characters to search'); return; }
        $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</td></tr>');
        $.ajax({
            url: '/offline-payments/search',
            type: 'GET',
            data: { q: q },
            success: function(data) {
                if (data.length === 0) {
                    $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-info-circle me-2"></i>No users found</td></tr>');
                    $('#offlineResultCount').text('0 results');
                    return;
                }
                $('#offlineResultCount').text(data.length + ' result(s)');
                var html = '';
                $.each(data, function(i, row) {
                    var paidBadges = '';
                    if (row.has_paid_coc) paidBadges += '<span class="badge bg-success me-1">COC Paid</span>';
                    if (row.has_paid_iut) paidBadges += '<span class="badge bg-success me-1">IUT Paid</span>';
                    if (!paidBadges) paidBadges = '<span class="badge bg-warning text-dark">No Payment</span>';
                    var typeBadge = row.type == 'student' ? '<span class="badge bg-info">Student</span>' : '<span class="badge bg-primary">Transfer</span>';
                    html += '<tr>';
                    html += '<td>'+(i+1)+'</td>';
                    html += '<td><strong>'+row.name+'</strong></td>';
                    html += '<td>'+row.identifier+'</td>';
                    html += '<td>'+typeBadge+'</td>';
                    html += '<td>'+(row.faculty || '-')+'</td>';
                    html += '<td>'+(row.department || '-')+'</td>';
                    html += '<td>'+paidBadges+'</td>';
                    html += '<td><button class="btn btn-sm btn-success openCreatePayment" data-uid="'+row.user_id+'" data-name="'+row.name+'" data-identifier="'+row.identifier+'" data-type="'+row.type+'"><i class="fas fa-plus-circle me-1"></i>Create Payment</button></td>';
                    html += '</tr>';
                });
                $('#offlinePaymentBody').html(html);
            },
            error: function() {
                $('#offlinePaymentBody').html('<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>Error searching</td></tr>');
            }
        });
    }

    $('#offlineSearchBtn').click(searchUsers);
    $('#offlineSearch').on('keypress', function(e) { if(e.which==13) searchUsers(); });

    $(document).on('click', '.openCreatePayment', function() {
        var btn = $(this);
        $('#modalUserName').text(btn.data('name'));
        $('#modalUserInfo').text(btn.data('type') == 'student' ? 'Student — ' + btn.data('identifier') : 'Transfer Applicant — ' + btn.data('identifier'));
        $('#modalUserId').val(btn.data('uid'));
        $('#modalFeeType').val('');
        $('#modalAmountDisplay').val('');
        $('#modalReference').val('');
        $('#modalReceipt').val('');
        // Show relevant options based on user type
        if (btn.data('type') == 'student') {
            $('#cocOptions').show();
            $('#iutOptions').hide();
        } else {
            $('#cocOptions').hide();
            $('#iutOptions').show();
        }
        var modal = new bootstrap.Modal(document.getElementById('createOfflinePaymentModal'));
        modal.show();
    });

    $('#modalFeeType').on('change', function() {
        var val = $(this).val();
        if (val && feeAmounts[val]) {
            $('#modalAmountDisplay').val('₦' + Number(feeAmounts[val]).toLocaleString('en-NG', {minimumFractionDigits:2}));
        } else {
            $('#modalAmountDisplay').val('');
        }
    });

    $('#createOfflineForm').on('submit', function(e) {
        e.preventDefault();
        if (!$('#modalFeeType').val()) { alert('Please select a fee type'); return; }
        if (!$('#modalReference').val().trim()) { alert('Please enter a payment reference'); return; }
        var formData = new FormData(this);
        $('#createPaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...');
        $.ajax({
            url: '/offline-payments/confirm',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('createOfflinePaymentModal')).hide();
                alert(res.message || 'Payment created successfully!');
                searchUsers();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message || 'Error') : 'Server error';
                alert('Error: ' + msg);
            },
            complete: function() {
                $('#createPaymentBtn').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Create Payment');
            }
        });
    });
});
</script>

<!-- Export Paid Students Modal -->
<div class="modal fade" id="exportPaidStudentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Paid Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportPaidStudentsForm" action="/admin/receipts/export-paid-students" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="exportSession">Select Session</label>
                        <select class="form-control" id="exportSession" name="session" required>
                            <option value="">Select Session</option>
                            <option value="2028/2029">2028/2029</option>
                            <option value="2027/2028">2027/2028</option>
                            <option value="2026/2027">2026/2027</option>
                            <option value="2025/2026">2025/2026</option>
                            <option value="2024/2025" selected>2024/2025</option>
                            <option value="2023/2024">2023/2024</option>
                            <option value="2022/2023">2022/2023</option>
                            <option value="2021/2022">2021/2022</option>
                            <option value="2020/2021">2020/2021</option>
                            <option value="2019/2020">2019/2020</option>
                            <option value="2018/2019">2018/2019</option>
                            <option value="2017/2018">2017/2018</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="exportSponsor">Sponsor</label>
                        <select class="form-control" id="exportSponsor" name="fees_type">
                            <option value="">All</option>
                            <option value="nelfund">NELFUND</option>
                            <option value="others">Others (Self Sponsor)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="exportPaidStudentsForm" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>
