<!-- Result Status Modal -->
<div id="resultStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="resultStatusLabel" aria-hidden="true">
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
                        <textarea class="form-control" id="statusResultCodes" name="resultCodes" rows="5" placeholder="e.g. ABC123, XYZ456
DEF789"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label for="statusSession">Session</label>
                        <select class="form-control" id="statusSession" name="session" required>
                            <option value="">Select Session</option>
                            @foreach($session as $row)
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
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="statusValue">Status</label>
                        <select class="form-control" id="statusValue" name="status" required>
                            <option value="">Select Status</option>
                            <option value="system">System (Lecturer Level)</option>
                            <option value="lecturer">Lecturer (Departmental Level)</option>
                            <option value="hod">HOD (Faculty Level)</option>
                            <option value="dean">Dean (Course System Level)</option>
                            <option value="cs">CS (VC Level)</option>
                            <option value="vc">VC (Approve)</option>
                        </select>
                    </div>
                    <div class="form-check mb-3 mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmStatusUpdate" name="confirmStatusUpdate" required>
                        <label class="form-check-label" for="confirmStatusUpdate">
                            I understand the implications of changing the approval status for these results
                        </label>
                    </div>
                    
                    <div id="statusProgress" style="display: none;">
                        <div class="progress mb-3">
                            <div id="statusProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="statusUpdateStatus" class="text-muted">Processing: 0/0 (0%)</div>
                        <div id="statusUpdateDetails" class="small text-muted"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="submitStatusForm" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize modal - only once
    if (!window.statusModalInitDone) {
        const statusModalEl = document.getElementById('resultStatus');
        if (statusModalEl) {
            window.statusModalInstance = new bootstrap.Modal(statusModalEl);
            console.log('Status modal initialized successfully');
        }
        window.statusModalInitDone = true;
    }

    // Add click handler for the submit button
    $(document).on('click', '#submitStatusForm', function() {
        console.log('Status submit button clicked');
        $('#resultStatusForm').submit();
    });

    // Add debug styles for modal
    const styleStatus = document.createElement('style');
    styleStatus.textContent = `
        #resultStatus {
            z-index: 9999 !important;
        }
        #resultStatus .modal-dialog {
            max-width: 800px;
        }
    `;
    document.head.appendChild(styleStatus);

    // Handle form submission
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
                const msg = 'Please confirm that you understand the implications of updating result status.';
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
            const codesArray = resultCodes.split(/[,\n]+/).map(code => code.trim()).filter(code => code);
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
                    return { success: false, error: err };
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
                        results.push({ code, success: false, result: null, error });
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
            for (const { code, success, result, error } of batchResults) {
                processed++;
                
                if (success) {
                    const updated = result.updated_count || 0;
                    successCount += updated;
                    
                    if (result.message) {
                        $('#statusUpdateDetails').append(`<div class="text-success">${code}: ${result.message}</div>`);
                    }
                } else {
                    failedCount++;
                    failedCodes.push(code);
                    const errorMsg = error?.responseJSON?.error || error?.statusText || 'Unknown error';
                    $('#statusUpdateDetails').append(`<div class="text-danger">${code}: ${errorMsg}</div>`);
                }
                
                // Update progress
                const progress = Math.min(100, Math.floor((processed / codesArray.length) * 100));
                const remaining = codesArray.length - processed;
                const processingText = remaining > 0 
                    ? `Processing: ${processed}/${codesArray.length} (${progress}%) - ${remaining} remaining`
                    : `Completed: ${processed}/${codesArray.length} (100%)`;
                    
                $('#statusProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                $('#statusUpdateStatus').text(processingText);
                
                // Auto-scroll to bottom of details
                const detailsDiv = document.getElementById('statusUpdateDetails');
                if (detailsDiv) {
                    detailsDiv.scrollTop = detailsDiv.scrollHeight;
                }
            }
            
            // Show final result
            let resultMessage = `Processed ${processed} codes (${successCount} records updated, ${failedCount} failed).`;
            if (failedCount > 0) {
                resultMessage += ' Failed codes: ' + failedCodes.join(', ');
            }
            $('#statusUpdateDetails').prepend(
                `<div class="alert ${failedCount > 0 ? 'alert-warning' : 'alert-success'}">${resultMessage}</div>`
            );
            
        } catch (error) {
            console.error('Error during status update:', error);
            const errorMsg = error?.responseJSON?.error || error?.statusText || 'An unknown error occurred';
            $('#statusUpdateDetails').prepend(
                `<div class="alert alert-danger">Error: ${errorMsg}</div>`
            );
        } finally {
            $btn.prop('disabled', false);
        }
    });
    
    // Clear form when modal is closed
    $('#resultStatus').on('hidden.bs.modal', function () {
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
