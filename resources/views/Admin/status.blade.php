<div class="page-wrapper">
    <script>
        // Basic check if script is running
        console.log('Status page script initialized');

        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
        } else {
            console.log('jQuery version:', jQuery.fn.jquery);
        }

        // Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
        } else {
            console.log('Bootstrap version:', bootstrap.Tooltip ? '5+' : 'Not found');
        }

        // Wait for document to be ready
        $(document).ready(function() {
            console.log('Document ready - status page loaded');

            // Debug: Check if button exists in DOM
            const $deleteBtn = $('#submitDeleteForm');
            if ($deleteBtn.length) {
                console.log('Delete button found in DOM');
            } else {
                console.log('Delete button not loaded yet - this is normal during initial page load');
            }

            // Initialize modal - only once
            if (!window.deleteModalInitDone) {
                const deleteModalEl = document.getElementById('deleteResults');
                if (!deleteModalEl) {
                    console.error('Delete modal element not found!');
                } else {
                    window.deleteModalInstance = new bootstrap.Modal(deleteModalEl);
                    console.log('Delete modal initialized successfully');
                }
                window.deleteModalInitDone = true;
            }

            // Debug modal events - attach only once
            if (!window.modalEventsAttached) {
                const modalEl = document.getElementById('deleteResults');
                if (modalEl) {
                    modalEl.addEventListener('show.bs.modal', function(event) {
                        console.log('Modal is about to be shown', event);
                    });

                    modalEl.addEventListener('shown.bs.modal', function(event) {
                        console.log('Modal is now shown', event);
                    });

                    window.modalEventsAttached = true;
                }
            }

            // Modal is already initialized in the code above
            // No need to reinitialize

            // Bootstrap will handle opening the modal via data-bs-toggle and data-bs-target
            // Add click handler for the submit button
            $(document).on('click', '#submitDeleteForm', function() {
                console.log('Submit button clicked');
                $('#deleteResultsForm').submit();
            });

            // Add debug styles for modal
            const style = document.createElement('style');
            style.textContent = `
        #deleteResults {
            z-index: 9999 !important;
        }
        #deleteResults .modal-dialog {
            max-width: 800px;
        }
    `;
            document.head.appendChild(style);

            // Debug: Log when document is ready
            console.log('Document ready - setting up form handler');

            // Handle form submission
            $('#deleteResultsForm').off('submit').on('submit', async function(e) {
                console.log('Form submit event triggered');
                e.preventDefault();
                console.log('Delete form submission started');

                // Prevent multiple clicks
                const $btn = $('#processDeleteBtn');
                console.log('Submit button found:', $btn.length > 0);
                $btn.prop('disabled', true);

                // Show we're starting the process
                const $progress = $('#deleteProgress');
                const $details = $('#deleteDetails');

                console.log('Progress element found:', $progress.length > 0);
                console.log('Details element found:', $details.length > 0);

                $progress.show();
                $details.html('<div class="alert alert-info">Starting deletion process...</div>');

                // Debug: Log form values
                console.log('Form values:', {
                    resultCodes: $('#resultCodes').val(),
                    session: $('#deleteSession').val(),
                    semester: $('#deleteSemester').val(),
                    confirmDelete: $('#confirmDelete').is(':checked')
                });

                try {
                    // Form validation
                    if (!$('#confirmDelete').is(':checked')) {
                        const msg = 'Please confirm that you understand this action cannot be undone.';
                        console.log('Validation failed:', msg);
                        alert(msg);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    const resultCodes = $('#resultCodes').val().trim();
                    const session = $('#deleteSession').val();
                    const semester = $('#deleteSemester').val();
                    const _token = $('input[name="_token"]').val(); // Get CSRF token

                    console.log('Form values:', {
                        resultCodes,
                        session,
                        semester
                    });

                    if (!resultCodes) {
                        const msg = 'Please enter at least one result code.';
                        console.log('Validation failed:', msg);
                        alert(msg);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!session) {
                        const msg = 'Please select a session.';
                        console.log('Validation failed:', msg);
                        alert(msg);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!semester) {
                        const msg = 'Please select a semester.';
                        console.log('Validation failed:', msg);
                        alert(msg);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    // Process batches
                    console.log('Starting batch processing');

                    // Prepare data
                    const codesArray = resultCodes.split(/[,\n]+/).map(code => code.trim()).filter(
                        code => code);
                    console.log('Codes to process:', codesArray);

                    if (codesArray.length === 0) {
                        const msg = 'Please enter at least one result code.';
                        console.log('Validation failed:', msg);
                        alert(msg);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    console.log(`Will process ${codesArray.length} code(s)`);
                    const totalCodes = codesArray.length;
                    // Show progress UI
                    $('#deleteDetails').empty();

                    // Initialize progress
                    let processed = 0;
                    let successCount = 0;
                    let failedCount = 0;
                    let failedCodes = [];

                    // Process deletion for a single code
                    const deleteCode = (code) => {
                        return $.ajax({
                            url: '/delete-result',
                            type: 'POST',
                            data: {
                                _token: _token, // Use the token from the form
                                code: code,
                                session: session,
                                semester: semester
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': _token
                            }
                        });
                    };

                    // Process a batch of codes
                    const processBatch = async (batch) => {
                        const results = [];
                        for (const code of batch) {
                            try {
                                const result = await deleteCode(code);
                                results.push({
                                    code,
                                    success: true,
                                    result
                                });
                            } catch (error) {
                                console.error('Error deleting code:', code, error);
                                results.push({
                                    code,
                                    success: false,
                                    error
                                });
                            }
                        }
                        return results;
                    };

                    console.log('Starting deletion for', codesArray.length, 'codes');
                    $('#deleteStatus').text(`Processing ${codesArray.length} codes...`);

                    // Process all codes in a single batch
                    const batchResults = await processBatch(codesArray);
                    console.log('Batch results:', batchResults);

                    // Process results
                    for (const {
                            code,
                            success,
                            result,
                            error
                        }
                        of batchResults) {
                        processed++;

                        if (success) {
                            const deleted = result.deleted_count || 0;
                            successCount += deleted;

                            if (result.message) {
                                $('#deleteDetails').append(
                                    `<div class="text-success">${code}: ${result.message}</div>`);
                            }
                        } else {
                            failedCount++;
                            failedCodes.push(code);
                            const errorMsg = error?.responseJSON?.error || error?.statusText ||
                                'Unknown error';
                            $('#deleteDetails').append(
                                `<div class="text-danger">${code}: ${errorMsg}</div>`);
                        }

                        // Update progress
                        const progress = Math.min(100, Math.floor((processed / codesArray.length) *
                            100));
                        const remaining = codesArray.length - processed;
                        const processingText = remaining > 0 ?
                            `Processing: ${processed}/${codesArray.length} (${progress}%) - ${remaining} remaining` :
                            `Completed: ${processed}/${codesArray.length} (100%)`;

                        $('#deleteProgressBar').css('width', progress + '%').attr('aria-valuenow',
                            progress);
                        $('#deleteStatus').text(processingText);

                        // Auto-scroll to bottom of details
                        const detailsDiv = document.getElementById('deleteDetails');
                        if (detailsDiv) {
                            detailsDiv.scrollTop = detailsDiv.scrollHeight;
                        }
                    }

                    // Show final result
                    let resultMessage =
                        `Processed ${processed} codes (${successCount} records deleted, ${failedCount} failed).`;
                    if (failedCount > 0) {
                        resultMessage += ' Failed codes: ' + failedCodes.join(', ');
                    }
                    $('#deleteDetails').prepend(
                        `<div class="alert ${failedCount > 0 ? 'alert-warning' : 'alert-success'}">${resultMessage}</div>`
                    );

                } catch (error) {
                    console.error('Error during deletion:', error);
                    const errorMsg = error?.responseJSON?.error || error?.statusText ||
                        'An unknown error occurred';
                    $('#deleteDetails').prepend(
                        `<div class="alert alert-danger">Error: ${errorMsg}</div>`
                    );
                } finally {
                    $btn.prop('disabled', false);
                }
            });

            // Clear form when modal is closed
            $('#deleteResults').on('hidden.bs.modal', function() {
                $('#resultCodes').val('');
                $('#confirmDelete').prop('checked', false);
                $('#deleteProgress').hide();
                $('#deleteProgressBar').css('width', '0%').attr('aria-valuenow', 0);
                $('#deleteStatus').text('Processing: 0/0 (0%)');
                $('#deleteDetails').empty();
                $('#processDeleteBtn').prop('disabled', false);
            });

            console.log('Event handlers initialized');

            // Result Status Modal Code
            // Initialize modal - only once
            if (!window.statusModalInitDone) {
                const statusModalEl = document.getElementById('resultStatus');
                if (statusModalEl) {
                    window.statusModalInstance = new bootstrap.Modal(statusModalEl);
                    console.log('Status modal initialized successfully');
                }
                window.statusModalInitDone = true;
            }

            // Add click handler for the status submit button
            $(document).on('click', '#submitStatusForm', function() {
                console.log('Status submit button clicked');
                $('#resultStatusForm').submit();
            });

            // Handle status form submission
            $('#resultStatusForm').off('submit').on('submit', async function(e) {
                console.log('Status form submit event triggered');
                e.preventDefault();
                console.log('Status form submission started');

                // Prevent multiple clicks
                const $btn = $('#submitStatusForm');
                console.log('Status submit button found:', $btn.length > 0);
                $btn.prop('disabled', true);

                // Show we're starting the process
                const $progress = $('#statusProgress');
                const $details = $('#statusUpdateDetails');

                console.log('Progress element found:', $progress.length > 0);
                console.log('Details element found:', $details.length > 0);

                $progress.show();
                $details.html('<div class="alert alert-info">Starting status update process...</div>');

                // Get form values
                const resultCodes = $('#statusResultCodes').val();
                const session = $('#statusSession').val();
                const semester = $('#statusSemester').val();
                const status = $('#statusValue').val();
                const _token = $('input[name="_token"]').val();

                // Debug: Log form values
                console.log('Form values:', {
                    resultCodes,
                    session,
                    semester,
                    status,
                    confirmStatusUpdate: $('#confirmStatusUpdate').is(':checked')
                });

                try {
                    // Form validation
                    if (!$('#confirmStatusUpdate').is(':checked')) {
                        const msg =
                            'Please confirm that you understand the implications of updating result status.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!resultCodes) {
                        const msg = 'Please enter at least one result code.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!session) {
                        const msg = 'Please select a session.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!semester) {
                        const msg = 'Please select a semester.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    if (!status) {
                        const msg = 'Please select a status value.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    // Process batches
                    console.log('Starting batch processing for status update');

                    // Prepare data
                    const codesArray = resultCodes.split(/[,\n]+/).map(code => code.trim()).filter(
                        code => code);
                    console.log('Codes to process:', codesArray);

                    if (codesArray.length === 0) {
                        const msg = 'No valid result codes found.';
                        console.log('Validation failed:', msg);
                        $('#statusUpdateDetails').html(`<div class="alert alert-danger">${msg}</div>`);
                        $btn.prop('disabled', false);
                        return false;
                    }

                    // Update progress text
                    $('#statusUpdateStatus').text(`Will process ${codesArray.length} code(s)`);

                    // Track processed codes
                    let processed = 0;
                    let successCount = 0;
                    let failedCount = 0;
                    const failedCodes = [];

                    // Function to update status for a single code
                    const updateStatus = async (code) => {
                        try {
                            return await $.ajax({
                                url: '/update-result-status',
                                type: 'POST',
                                data: {
                                    _token: _token,
                                    code: code,
                                    session: session,
                                    semester: semester,
                                    status: status
                                },
                                dataType: 'json',
                                headers: {
                                    'X-CSRF-TOKEN': _token
                                }
                            });
                        } catch (err) {
                            return {
                                success: false,
                                error: err
                            };
                        }
                    };

                    // Process a batch of codes
                    const processBatch = async (batch) => {
                        const results = [];
                        for (const code of batch) {
                            try {
                                const response = await updateStatus(code);
                                results.push({
                                    code,
                                    success: response.success,
                                    result: response,
                                    error: null
                                });
                            } catch (error) {
                                results.push({
                                    code,
                                    success: false,
                                    result: null,
                                    error
                                });
                            }
                        }
                        return results;
                    };

                    console.log('Starting status update for', codesArray.length, 'codes');
                    $('#statusUpdateStatus').text(`Processing ${codesArray.length} codes...`);

                    // Process all codes in a single batch
                    const batchResults = await processBatch(codesArray);
                    console.log('Batch results:', batchResults);

                    // Process results
                    for (const {
                            code,
                            success,
                            result,
                            error
                        }
                        of batchResults) {
                        processed++;

                        if (success) {
                            const updated = result.updated_count || 0;
                            successCount += updated;

                            if (result.message) {
                                $('#statusUpdateDetails').append(
                                    `<div class="text-success">${code}: ${result.message}</div>`);
                            }
                        } else {
                            failedCount++;
                            failedCodes.push(code);
                            const errorMsg = error?.responseJSON?.error || error?.statusText ||
                                'Unknown error';
                            $('#statusUpdateDetails').append(
                                `<div class="text-danger">${code}: ${errorMsg}</div>`);
                        }

                        // Update progress
                        const progress = Math.min(100, Math.floor((processed / codesArray.length) *
                            100));
                        const remaining = codesArray.length - processed;
                        const processingText = remaining > 0 ?
                            `Processing: ${processed}/${codesArray.length} (${progress}%) - ${remaining} remaining` :
                            `Completed: ${processed}/${codesArray.length} (100%)`;

                        $('#statusProgressBar').css('width', progress + '%').attr('aria-valuenow',
                            progress);
                        $('#statusUpdateStatus').text(processingText);

                        // Auto-scroll to bottom of details
                        const detailsDiv = document.getElementById('statusUpdateDetails');
                        if (detailsDiv) {
                            detailsDiv.scrollTop = detailsDiv.scrollHeight;
                        }
                    }

                    // Show final result
                    let resultMessage =
                        `Processed ${processed} codes (${successCount} records updated, ${failedCount} failed).`;
                    if (failedCount > 0) {
                        resultMessage += ' Failed codes: ' + failedCodes.join(', ');
                    }
                    $('#statusUpdateDetails').prepend(
                        `<div class="alert ${failedCount > 0 ? 'alert-warning' : 'alert-success'}">${resultMessage}</div>`
                    );

                } catch (error) {
                    console.error('Error during status update:', error);
                    const errorMsg = error?.responseJSON?.error || error?.statusText ||
                        'An unknown error occurred';
                    $('#statusUpdateDetails').prepend(
                        `<div class="alert alert-danger">Error: ${errorMsg}</div>`
                    );
                } finally {
                    $btn.prop('disabled', false);
                }
            });

            // Clear form when modal is closed
            $('#resultStatus').on('hidden.bs.modal', function() {
                $('#statusResultCodes').val('');
                $('#confirmStatusUpdate').prop('checked', false);
                $('#statusProgress').hide();
                $('#statusProgressBar').css('width', '0%').attr('aria-valuenow', 0);
                $('#statusUpdateStatus').text('Processing: 0/0 (0%)');
                $('#statusUpdateDetails').empty();
                $('#submitStatusForm').prop('disabled', false);
            });
        });
    </script>

    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ strtoupper($page) }}</h5>
                </div>
                <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #5D87FF; margin: 20px 0;">
                    <ul>
                        <li>
                            Users are required to upload the academic status for the 2022/2023 session. For example,
                            the academic status of current Part 2 students should be uploaded under their Part 1
                            status.
                        </li>
                        <li>
                            Carryovers are separate with comma even the last carryover e.g CMC101,GMC101,TMT304
                        </li>
                    </ul>
                </div>
                <div class="card-block">
                    <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                        data-bs-target="#import"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                    <a href="{{ url('uploads/STATUS TEMPLATE UNIMAID.xlsx') }}" class="btn btn-primary"
                        download="STATUS TEMPLATE.xlsx"><i class="fas fa-download"></i> Download Template</a>

                    @if (strtoupper(session('username')) == 'SP11913' || strtoupper(session('username')) == 'SU')
                        <button href="#" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#applyResult"><i class="fas fa-file"></i>
                            {{ 'Apply Approved Result' }}</button>

                        <button href="#" class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#promoteStudent"><i class="fas fa-user-plus"></i>
                            {{ 'Promote Student' }}</button>

                        <button href="#" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#courseReg"><i class="fas fa-gear"></i>
                            {{ 'Course Registration' }}</button>

                        <button href="#" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#deCourse"><i class="fas fa-gear"></i>
                            {{ 'DE Course Registration' }}</button>

                        <button href="#" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#reRegistration"><i class="fas fa-redo"></i>
                            {{ 'Re-Registration' }}</button>

                        <a href="/session history" class="btn btn-primary"><i class="fas fa-gear"></i>
                            {{ 'Session History' }}</a>

                        <a href="/student course registration" class="btn btn-primary"><i class="fas fa-book"></i>
                            {{ 'Student Course Registration' }}</a>

                        <button href="#" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteResults"><i class="fas fa-trash"></i>
                            {{ 'Delete Results' }}</button>

                        <button href="#" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#resultStatus"><i class="fas fa-edit"></i>
                            {{ 'Result Status' }}</button>
                    @endif
                </div>
                <div class="card-block">
                    <form class="needs-validation" novalidate method="GET" action="/print-status-pdf">
                        @csrf
                        <div class="row gx-2">
                            <div class="form-group col-md-2">
                                <label for="facultyf">Faculty <span>*</span></label>
                                <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
                                    <option value="">Select Option</option>
                                    @foreach ($faculty as $roww)
                                        <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="departmentf">Department</label>
                                <select class="form-control department" lang="f" id="departmentf"
                                    name="department">
                                    <option value="">Select Faculty First</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="programf">Program</label>
                                <select class="form-control" id="programf" lang="f" name="program">
                                    <option value="">Select Department First</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="session">Session</label>
                                <select class="form-control" id="session" name="session">
                                    <option value="">Select Option</option>
                                    @foreach ($session as $ses)
                                        <option value="{{ $ses->title }}">{{ $ses->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-1">
                                <label for="level">Level</label>
                                <select class="form-control" id="level" name="level" required>
                                    <option value="">Select Option</option>
                                    @for ($i = 1; $i <= 7; $i++)
                                        <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <input type="hidden" name="type" value="FINAL">
                            <div class="form-group col-md-1">
                                <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                    {{ 'Filter' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
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

                <form class="form-group" action="upload status" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1" name="department"
                                lang="1" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control" id="program1" name="program" lang="1" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level">
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mode">Select Mode</label>
                            <select class="form-control" id="mode" name="mode">
                                <option value="">Select Option</option>
                                <option value="regular">Regular</option>
                                <option value="spill">Spill</option>
                            </select>
                        </div>
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
<div id="applyResult" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Apply Approved Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="/apply-result" method="GET" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="facultyf">Faculty <span>*</span></label>
                            <select class="form-control faculty" lang="ff" name="faculty" id="facultyff">
                                <option value="">Select Option</option>
                                @foreach ($faculty as $roww)
                                    <option value="{{ $roww->code }}">{{ $roww->title }} ({{ $roww->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="departmentf">Department</label>
                            <select class="form-control department" lang="ff" id="departmentff"
                                name="department" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="applyByDept"
                                    name="by_department" value="1">
                                <label class="form-check-label" for="applyByDept">
                                    <strong>By Department</strong> <small class="text-muted">(all programs in
                                        department)</small>
                                </label>
                            </div>
                        </div>
                        {{-- level --}}
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" name="levels" id="level" required>
                                <option value="">Select Level</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="300">300</option>
                                <option value="400">400</option>
                                <option value="500">500</option>
                                <option value="600">600</option>
                                <option value="700">700</option>
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Apply Approved Results</button>
                    </div>
                </form>
                {{-- <a href="/apply-result" class="btn btn-warning">Apply Approved Results</a> --}}
            </div>
        </div>

    </div>
</div>
<div id="promoteStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Promote Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <form class="form-group" action="/generate-status" method="GET" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1p" name="faculty" lang="1p"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1p" name="department"
                                lang="1p" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1p">Program</label>
                            <select class="form-control" id="program1p" name="program" lang="1p" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level">
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                @endfor
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Process</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div id="reRegistration" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Re-Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <form class="form-group" action="/re-register" method="GET" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1re" name="faculty" lang="1re"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1re" name="department"
                                lang="1re" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1re">Program</label>
                            <select class="form-control" id="program1re" name="program" lang="1re" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level">
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i * 100 }}">{{ $i * 100 }}</option>
                                @endfor
                            </select>
                            <label for="session">Session</label>
                            <select class="form-control" id="session" name="session">
                                <option value="">Select Option</option>
                                @foreach ($session as $row)
                                    <option value="{{ $row->title }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Re-Register</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div id="deleteResults" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteResultsLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="deleteResultsForm" action="javascript:void(0);">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteResultsLabel">Delete Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This action will permanently delete the selected results. This cannot
                        be undone.
                    </div>
                    <div class="form-group">
                        <label for="resultCodes">Enter Result Codes (one per line or comma-separated)</label>
                        <textarea class="form-control" id="resultCodes" name="resultCodes" rows="5"
                            placeholder="e.g. ABC123, XYZ456
    DEF789"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label for="deleteSession">Session</label>
                        <select class="form-control" id="deleteSession" name="session" required>
                            <option value="">Select Session</option>
                            @foreach ($session as $row)
                                <option value="{{ $row->title }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="deleteSemester">Semester</label>
                        <select class="form-control" name="semester" id="deleteSemester">
                            <option value="">Select Semester</option>
                            <option value="FIRST">FIRST</option>
                            <option value="SECOND">SECOND</option>
                            <option value="THIRD">THIRD</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDelete" name="confirmDelete"
                            required>
                        <label class="form-check-label" for="confirmDelete">
                            I understand that this action cannot be undone and I have verified the result codes
                        </label>
                    </div>

                    <div id="deleteProgress" style="display: none;">
                        <div class="progress mb-3">
                            <div id="deleteProgressBar"
                                class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%"></div>
                        </div>
                        <div id="deleteStatus" class="text-muted">Processing: 0/0 (0%)</div>
                        <div id="deleteDetails" class="small text-muted"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="submitDeleteForm" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Process Deletion
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Result Status Modal -->
<div id="resultStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="resultStatusLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="resultStatusForm" action="javascript:void(0);">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="resultStatusLabel">
                        <i class="fas fa-edit me-2"></i> Update Result Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Info:</strong> This action will update the approval status of the selected results.
                    </div>
                    <div class="form-group">
                        <label for="statusResultCodes">Enter Result Codes (one per line or comma-separated)</label>
                        <textarea class="form-control" id="statusResultCodes" name="resultCodes" rows="5"
                            placeholder="e.g. ABC123, XYZ456
    DEF789"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label for="statusSession">Session</label>
                        <select class="form-control" id="statusSession" name="session" required>
                            <option value="">Select Session</option>
                            @foreach ($session as $row)
                                <option value="{{ $row->title }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="statusSemester">Semester</label>
                        <select class="form-control" id="statusSemester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="FIRST">FIRST</option>
                            <option value="SECOND">SECOND</option>
                            <option value="THIRD">THIRD</option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="statusValue">Status</label>
                        <select class="form-control" id="statusValue" name="status" required>
                            <option value="">Select Status</option>
                            <option value="system">Lecturer Level</option>
                            <option value="lecturer">Departmental Level</option>
                            <option value="hod">Faculty Level</option>
                            <option value="dean">Course System Level</option>
                            <option value="cs">VC Level</option>
                            <option value="vc">Approve</option>
                        </select>
                    </div>
                    <div class="form-check mb-3 mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmStatusUpdate"
                            name="confirmStatusUpdate" required>
                        <label class="form-check-label" for="confirmStatusUpdate">
                            I understand the implications of changing the approval status for these results
                        </label>
                    </div>

                    <div id="statusProgress" style="display: none;">
                        <div class="progress mb-3">
                            <div id="statusProgressBar"
                                class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%"></div>
                        </div>
                        <div id="statusUpdateStatus" class="text-muted">Processing: 0/0 (0%)</div>
                        <div id="statusUpdateDetails" class="small text-muted"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" id="submitStatusForm" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
