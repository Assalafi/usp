@if (in_array(strtoupper((string) session('username')), ['SP11913', 'SU'], true))
    <div id="importPayment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="importPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title mb-1" id="importPaymentLabel">Upload payment records</h5>
                        <small>Import payment records into the collection.</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('/upload-payment') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadService">Service</label>
                            <select class="form-select" name="service" id="paymentUploadService" required>
                                <option value="">Select service</option>
                                <option value="school fees">School Fees</option>
                                <option value="hostel fees">Hostel Fees</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadType">Payment type</label>
                            <select class="form-select" name="upload_type" id="paymentUploadType" required>
                                <option value="">Select payment type</option>
                                <option value="full">Full Payment</option>
                                <option value="part">Part Payment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadSponsor">Sponsor</label>
                            <select class="form-select" name="sponsor" id="paymentUploadSponsor" required>
                                <option value="">Select sponsor</option>
                                <option value="self sponsor">Self Sponsor</option>
                                <option value="nelfund">NELFUND</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadSession">Session</label>
                            <select class="form-select" name="session" id="paymentUploadSession" required>
                                <option value="">Select session</option>
                                @foreach (($session ?? collect()) as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paymentUploadFile">Excel file</label>
                            <input type="file" name="file" id="paymentUploadFile" accept=".xlsx,.xls" class="form-control" required>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">
                            Confirm the service, payment type, sponsor and session before uploading. The imported records will appear in this collection.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload records</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<details class="fw-tools-panel" id="offlineTools"><summary>Record an offline transfer payment</summary>
@if ($isAdmin)
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
@endif
</details>
@if ($isAdmin)
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
                                    $serviceTypes = \Illuminate\Support\Facades\Cache::remember('fees_payment_service_options', 3600, fn () => DB::table('invoices')
                                        ->select('serviceTypeId', 'description')
                                        ->whereNotNull('rrr')
                                        ->distinct()
                                        ->get());
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


<!-- Export Active Unpaid Students Modal -->
<div class="modal fade" id="exportUnpaidStudentsModal" tabindex="-1" role="dialog" aria-labelledby="exportUnpaidStudentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <div>
                    <h5 class="modal-title mb-1" id="exportUnpaidStudentsLabel">Export students with outstanding fees</h5>
                    <small class="text-dark">Use any combination of filters. The workbook includes the matching students and a totals summary.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportUnpaidStudentsForm" action="{{ route('admin.receipts.export_unpaid_students') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        Unpaid records include active students with a result or session-history record in the selected session.
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="exportUnpaidSession">Session <span class="text-danger">*</span></label>
                            <select class="form-control" id="exportUnpaidSession" name="session" required>
                                <option value="">Select session</option>
                                @foreach ($session as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="exportUnpaidSponsor">Sponsor</label>
                            <select class="form-control" id="exportUnpaidSponsor" name="fees_type">
                                <option value="">All sponsors</option>
                                <option value="nelfund">NELFUND</option>
                                <option value="others">Others (Self-sponsored)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="facultyexportUnpaid">Faculty</label>
                            <select class="form-control faculty" lang="exportUnpaid" id="facultyexportUnpaid" name="faculty">
                                <option value="">All faculties</option>
                                @foreach (($faculty ?? collect()) as $facultyOption)
                                    <option value="{{ $facultyOption->code }}">{{ $facultyOption->title }} ({{ $facultyOption->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="departmentexportUnpaid">Department</label>
                            <select class="form-control department" lang="exportUnpaid" id="departmentexportUnpaid" name="department">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="programexportUnpaid">Programme</label>
                            <select class="form-control" id="programexportUnpaid" name="program">
                                <option value="">All programmes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="levelExportUnpaid">Level</label>
                            <select class="form-control" id="levelExportUnpaid" name="level">
                                <option value="">All levels</option>
                                @foreach (($exportLevels ?? collect()) as $exportLevel)
                                    <option value="{{ $exportLevel }}">{{ $exportLevel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3"><i class="fas fa-file-excel me-1"></i>The exported workbook contains the filtered rows, totals, filters used, and outstanding amount summary.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="exportUnpaidStudentsButton">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Paid Students Modal -->
<div class="modal fade" id="exportPaidStudentsModal" tabindex="-1" role="dialog" aria-labelledby="exportPaidStudentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <div>
                    <h5 class="modal-title mb-1" id="exportPaidStudentsLabel">Export students who paid</h5>
                    <small>Filter the paid programme-fee records before downloading the workbook.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportPaidStudentsForm" action="{{ route('admin.receipts.export_paid_students') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="exportPaidSession">Session <span class="text-danger">*</span></label>
                            <select class="form-control" id="exportPaidSession" name="session" required>
                                <option value="">Select session</option>
                                @foreach ($session as $sessionOption)
                                    <option value="{{ $sessionOption->title }}" {{ $sessionOption->title === ($fees_session ?? session('system_session')) ? 'selected' : '' }}>{{ $sessionOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="exportPaidSponsor">Sponsor</label>
                            <select class="form-control" id="exportPaidSponsor" name="fees_type">
                                <option value="">All sponsors</option>
                                <option value="nelfund">NELFUND</option>
                                <option value="others">Others (Self-sponsored)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="facultyexportPaid">Faculty</label>
                            <select class="form-control faculty" lang="exportPaid" id="facultyexportPaid" name="faculty">
                                <option value="">All faculties</option>
                                @foreach (($faculty ?? collect()) as $facultyOption)
                                    <option value="{{ $facultyOption->code }}">{{ $facultyOption->title }} ({{ $facultyOption->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="departmentexportPaid">Department</label>
                            <select class="form-control department" lang="exportPaid" id="departmentexportPaid" name="department">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="programexportPaid">Programme</label>
                            <select class="form-control" id="programexportPaid" name="program">
                                <option value="">All programmes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="levelExportPaid">Level</label>
                            <select class="form-control" id="levelExportPaid" name="level">
                                <option value="">All levels</option>
                                @foreach (($exportLevels ?? collect()) as $exportLevel)
                                    <option value="{{ $exportLevel }}">{{ $exportLevel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3"><i class="fas fa-file-excel me-1"></i>With a faculty, department, programme or level selected, the workbook includes matching students even when they have no payment. IDs follow NN/NN/NN/NNNN, with an optional final letter; records without a valid matric number are omitted. Each row is marked No payment, Partially paid, Fully paid or Overpaid, with a complete summary.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="exportPaidStudentsForm" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


