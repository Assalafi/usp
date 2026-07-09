@php
    $hasExistingRrr = isset($existingRrr) && $existingRrr;
    $existingAmount = $existingInvoiceAmount ?? null;
    $vFee = $voluntaryFee ?? 100000;
    $oFee = $obligatoryFee ?? 50000;
    // Auto-detect transfer type from existing invoice amount
    $detectedType = null;
    if ($hasExistingRrr && $existingAmount) {
        $detectedType = ($existingAmount >= $vFee) ? 'voluntary' : 'obligatory';
    }
    // app status 1 for open, 0 for closed
    $applicationStatus = 0;
@endphp
@if($applicationStatus == 1)
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <!-- Page Header -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-4"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <img src="{{ asset('uploads/logo.png') }}" alt="UNIMAID Logo" style="height: 60px;" class="mb-3">
                        <h3 class="mb-1">INTER DEPARTMENTAL TRANSFER</h3>
                        @if (isset($application))
                            <h5 class="mb-0">Application No: {{ $application->application_no }}</h5>
                        @else
                            <h5 class="mb-0">Processing Fee Payment</h5>
                        @endif
                    </div>
                </div>

                @if (isset($application))
                    <!-- Application Summary -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Student Name:</strong></p>
                                    <p>{{ $application->student_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Student ID:</strong></p>
                                    <p>{{ $application->username }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>From:</strong></p>
                                    <p class="text-muted">
                                        {{ DB::table('department')->where('code', $application->current_department)->value('title') }}<br>
                                        <small>{{ DB::table('faculty')->where('code', $application->current_faculty)->value('title') }}</small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>To:</strong></p>
                                    <p class="text-primary">
                                        {{ DB::table('department')->where('code', $application->new_department)->value('title') }}<br>
                                        <small>{{ DB::table('faculty')->where('code', $application->new_faculty)->value('title') }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- SCENARIO 1: Existing RRR found - show pending invoice --}}
                @if ($hasExistingRrr)
                    <div class="card shadow-sm mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Invoice Found</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-3">
                                <p class="mb-2">You have an unpaid invoice for <strong>{{ $detectedType == 'voluntary' ? 'Voluntary' : 'Obligatory' }}</strong> transfer.</p>
                                <h2 class="text-primary mb-3">₦{{ number_format($existingAmount, 2) }}</h2>
                                <p class="text-muted mb-1"><strong>RRR:</strong> <code style="font-size:16px">{{ $existingRrr }}</code></p>

                                <div class="mb-4 mt-3">
                                    <p><strong>Student:</strong> {{ $student->fullname }}</p>
                                    <p><strong>Email:</strong> {{ $student->contact_email ?? 'Not provided' }}</p>
                                </div>

                                <button type="button" class="btn btn-success btn-lg" id="payExistingBtn">
                                    <i class="fas fa-lock me-2"></i>Pay ₦{{ number_format($existingAmount, 2) }} with Remita
                                </button>
                                <button type="button" class="btn btn-info btn-lg ms-2" id="verifyButton">
                                    <i class="fas fa-check-circle me-2"></i>Verify Payment
                                </button>

                                <div class="mt-3">
                                    <small class="text-muted"><i class="fas fa-shield-alt me-1"></i>Secure payment powered by Remita</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-redo me-2"></i>Want a different transfer type?</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Select a different type below and generate a new invoice.</p>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="d-block" style="cursor:pointer;">
                                        <div class="card transfer-card" id="voluntaryCard" style="border: 3px solid {{ $detectedType == 'voluntary' ? '#28a745' : '#dee2e6' }}; transition: all 0.2s;">
                                            <div class="card-body text-center py-3">
                                                <input type="radio" name="transfer_type" value="voluntary" id="voluntaryRadio" style="transform: scale(1.5); margin-bottom: 8px;" {{ $detectedType == 'voluntary' ? 'checked' : '' }}>
                                                <h6 class="mb-1"><i class="fas fa-hand-pointer me-1"></i> Voluntary</h6>
                                                <h4 class="text-primary mb-0">₦{{ number_format($vFee, 2) }}</h4>
                                                <small class="text-muted">Student-initiated transfer</small>
                                                @if($detectedType == 'voluntary') <br><span class="badge bg-success mt-1">Current Invoice</span> @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="d-block" style="cursor:pointer;">
                                        <div class="card transfer-card" id="obligatoryCard" style="border: 3px solid {{ $detectedType == 'obligatory' ? '#28a745' : '#dee2e6' }}; transition: all 0.2s;">
                                            <div class="card-body text-center py-3">
                                                <input type="radio" name="transfer_type" value="obligatory" id="obligatoryRadio" style="transform: scale(1.5); margin-bottom: 8px;" {{ $detectedType == 'obligatory' ? 'checked' : '' }}>
                                                <h6 class="mb-1"><i class="fas fa-university me-1"></i> Obligatory</h6>
                                                <h4 class="text-primary mb-0">₦{{ number_format($oFee, 2) }}</h4>
                                                <small class="text-muted">Institution-required transfer</small>
                                                @if($detectedType == 'obligatory') <br><span class="badge bg-success mt-1">Current Invoice</span> @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" id="newInvoiceBtn">
                                    <i class="fas fa-file-invoice me-2"></i>Generate New Invoice
                                </button>
                            </div>
                        </div>
                    </div>

                {{-- SCENARIO 2: No existing RRR, no application - fresh payment --}}
                @elseif (!isset($application))
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Processing Fee:</strong> A non-refundable fee is required to process your Inter-Departmental Transfer application.
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold"><i class="fas fa-exchange-alt me-2"></i>Select Transfer Type:</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="d-block" style="cursor:pointer;">
                                            <div class="card transfer-card" id="voluntaryCard" style="border: 3px solid #dee2e6; transition: all 0.2s;">
                                                <div class="card-body text-center py-3">
                                                    <input type="radio" name="transfer_type" value="voluntary" id="voluntaryRadio" style="transform: scale(1.5); margin-bottom: 8px;">
                                                    <h6 class="mb-1"><i class="fas fa-hand-pointer me-1"></i> Voluntary</h6>
                                                    <h4 class="text-primary mb-0">₦{{ number_format($vFee, 2) }}</h4>
                                                    <small class="text-muted">Student-initiated transfer</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="d-block" style="cursor:pointer;">
                                            <div class="card transfer-card" id="obligatoryCard" style="border: 3px solid #dee2e6; transition: all 0.2s;">
                                                <div class="card-body text-center py-3">
                                                    <input type="radio" name="transfer_type" value="obligatory" id="obligatoryRadio" style="transform: scale(1.5); margin-bottom: 8px;">
                                                    <h6 class="mb-1"><i class="fas fa-university me-1"></i> Obligatory</h6>
                                                    <h4 class="text-primary mb-0">₦{{ number_format($oFee, 2) }}</h4>
                                                    <small class="text-muted">Institution-required transfer</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center py-4">
                                <h2 class="mb-3">Amount to Pay</h2>
                                <h1 class="text-primary mb-4" id="displayAmount">-- Select Transfer Type --</h1>

                                <div class="mb-4">
                                    <p><strong>Student Name:</strong> {{ $student->fullname }}</p>
                                    <p><strong>Email:</strong> {{ $student->contact_email ?? 'Not provided' }}</p>
                                    <p><strong>Phone:</strong> {{ $student->contact_phone ?? 'Not provided' }}</p>
                                </div>

                                <button type="button" class="btn btn-success btn-lg" id="payButton">
                                    <i class="fas fa-lock me-2"></i>Pay with Remita
                                </button>
                                <button type="button" class="btn btn-info btn-lg ms-2" id="verifyButton">
                                    <i class="fas fa-check-circle me-2"></i>Verify Payment
                                </button>

                                <div class="mt-3">
                                    <small class="text-muted"><i class="fas fa-shield-alt me-1"></i>Secure payment powered by Remita</small>
                                </div>
                            </div>
                        </div>
                    </div>

                {{-- SCENARIO 3: Application exists but unpaid --}}
                @else
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Complete Payment</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <h2 class="mb-3">Amount to Pay</h2>
                                <h1 class="text-primary mb-4">₦{{ number_format($application->amount, 2) }}</h1>

                                <div class="mb-4">
                                    <p><strong>Student Name:</strong> {{ $student->fullname }}</p>
                                    <p><strong>Email:</strong> {{ $student->contact_email ?? 'Not provided' }}</p>
                                    <p><strong>Phone:</strong> {{ $student->contact_phone ?? 'Not provided' }}</p>
                                </div>

                                <button type="button" class="btn btn-success btn-lg" id="payButton">
                                    <i class="fas fa-lock me-2"></i>Pay with Remita
                                </button>
                                <button type="button" class="btn btn-info btn-lg ms-2" id="verifyButton">
                                    <i class="fas fa-check-circle me-2"></i>Verify Payment
                                </button>

                                <div class="mt-3">
                                    <small class="text-muted"><i class="fas fa-shield-alt me-1"></i>Secure payment powered by Remita</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm mt-4 mb-4">
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                            <ul class="mb-0">
                                <li>Payment is processed securely through Remita payment gateway</li>
                                <li>After successful payment, your application will be forwarded for approval</li>
                                <li>Keep your payment receipt for reference</li>
                                <li>Contact the registry if payment is not reflected within 24 hours</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if($applicationStatus == 0)
<div class="main-body">
    <div class="page-wrapper">
        <div class="page-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Change of Course Payment</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Application Closed</h5>
                                <p>The change of course application is currently closed. Please contact the registry for more information.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<!-- Loading Overlay -->
<div id="loadingOverlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="mt-3">Initializing payment...</h5>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl() }}/payment/v1/remita-pay-inline.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var voluntaryFee = {{ $vFee }};
    var obligatoryFee = {{ $oFee }};

    // ---- Card selection UI via radio change ----
    $('input[name="transfer_type"]').on('change', function() {
        var selected = $(this).val();
        // Reset both cards
        $('.transfer-card').css('border-color', '#dee2e6').css('background-color', '#fff');
        // Highlight selected card
        if (selected === 'voluntary') {
            $('#voluntaryCard').css('border-color', '#0d6efd').css('background-color', '#f0f4ff');
            if ($('#displayAmount').length) {
                $('#displayAmount').text('₦' + Number(voluntaryFee).toLocaleString('en-NG', { minimumFractionDigits: 2 }));
            }
        } else {
            $('#obligatoryCard').css('border-color', '#0d6efd').css('background-color', '#f0f4ff');
            if ($('#displayAmount').length) {
                $('#displayAmount').text('₦' + Number(obligatoryFee).toLocaleString('en-NG', { minimumFractionDigits: 2 }));
            }
        }
    });
    // Trigger for any pre-checked radio on page load
    $('input[name="transfer_type"]:checked').trigger('change');

    // ---- Pay existing RRR directly ----
    $('#payExistingBtn').click(function() {
        makePayment('{{ $existingRrr ?? '' }}', {{ $existingAmount ?? 0 }}, '{{ env('REMITA_MERCHANT_ID') }}');
    });

    // ---- Generate new invoice with different type ----
    $('#newInvoiceBtn').click(function() {
        var selectedType = $('input[name="transfer_type"]:checked').val();
        if (!selectedType) {
            Swal.fire({ icon: 'warning', title: 'Select Transfer Type', text: 'Please select a transfer type first.' });
            return;
        }
        @if ($hasExistingRrr)
            var currentType = '{{ $detectedType }}';
            if (selectedType === currentType) {
                Swal.fire({ icon: 'info', title: 'Same Type', text: 'This is the same as your current invoice. Use the Pay button above.' });
                return;
            }
        @endif
        initializePayment(selectedType);
    });

    // ---- Fresh payment (no existing RRR) ----
    $('#payButton').click(function() {
        @if (!isset($application))
            var selectedType = $('input[name="transfer_type"]:checked').val();
            if (!selectedType) {
                Swal.fire({ icon: 'warning', title: 'Select Transfer Type', text: 'Please select a transfer type before proceeding.' });
                return;
            }
            initializePayment(selectedType);
        @else
            initializePayment('voluntary');
        @endif
    });

    // ---- Verify Payment ----
    $('#verifyButton').click(function() {
        Swal.fire({
            title: 'Enter RRR',
            input: 'text',
            inputLabel: 'Remita Retrieval Reference',
            inputPlaceholder: 'Enter your RRR number',
            inputValue: '{{ $existingRrr ?? '' }}',
            showCancelButton: true,
            confirmButtonText: 'Verify',
            preConfirm: (rrr) => {
                if (!rrr) { Swal.showValidationMessage('Please enter an RRR'); return false; }
                return rrr;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                window.location.href = '{{ route('change-of-course.verify') }}?rrr=' + result.value;
            }
        });
    });

    // ---- Initialize Payment via AJAX ----
    function initializePayment(transferType) {
        $('#loadingOverlay').show();

        @if (isset($application))
            var paymentUrl = '{{ route('change-of-course.initialize-payment', $application->id ?? 0) }}';
        @else
            var paymentUrl = '{{ route('change-of-course.initialize-initial-payment') }}';
        @endif

        $.ajax({
            url: paymentUrl,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', transfer_type: transferType },
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success && response.rrr) {
                    makePayment(response.rrr, response.amount, response.merchantId);
                } else {
                    Swal.fire({ icon: 'error', title: 'Payment Failed', text: response.error || 'Unable to initialize payment.' });
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                console.error('Payment error:', xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
            }
        });
    }

    // ---- Launch Remita Payment Widget ----
    function makePayment(rrr, amount, merchantId) {
        try {
            var paymentEngine = RmPaymentEngine.init({
                key: "{{ env('REMITA_PUBLIC_KEY') }}",
                processRrr: true,
                transactionId: Math.floor(Math.random() * 1101233),
                extendedData: { customFields: [{ name: "rrr", value: rrr }] },
                onSuccess: function(response) {
                    window.location.href = '{{ route('change-of-course.verify') }}?rrr=' + rrr;
                },
                onError: function(response) {
                    Swal.fire({ icon: 'error', title: 'Payment Failed', text: 'Payment was not completed. Please try again.' });
                },
                onClose: function() { console.log('Payment window closed'); }
            });
            paymentEngine.showPaymentWidget();
        } catch (error) {
            console.error('Payment init error:', error);
            Swal.fire({ icon: 'error', title: 'Payment Error', text: 'Could not initialize payment. Please try again.' });
        }
    }
});
</script>
