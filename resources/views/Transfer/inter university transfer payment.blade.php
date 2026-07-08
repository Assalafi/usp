<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Inter-University Transfer - Payment</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dash"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="/inter-university-transfer">Transfer</a></li>
                            <li class="breadcrumb-item"><a href="#!">Payment</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card mr-2"></i> Transfer Fee Payment</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Payment Information:</strong>
                            <table class="table table-sm table-borderless mt-2 mb-0">
                                <tr>
                                    <td><i class="fas fa-arrow-right mr-1"></i> Within Nigeria:</td>
                                    <td><strong>&#8358;{{ number_format($withinNigeriaFee, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-arrow-right mr-1"></i> From Abroad:</td>
                                    <td><strong>&#8358;{{ number_format($abroadFee, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>

                        <div class="form-group mb-4">
                            <label><strong>Select Transfer Type <span class="text-danger">*</span></strong></label>
                            <select class="form-control" id="transferType" required>
                                <option value="">-- Select Transfer Type --</option>
                                <option value="within_nigeria">Within Nigeria -
                                    &#8358;{{ number_format($withinNigeriaFee, 2) }}</option>
                                <option value="abroad">From Abroad - &#8358;{{ number_format($abroadFee, 2) }}</option>
                            </select>
                        </div>

                        <div id="paymentAmount" class="text-center mb-4" style="display:none;">
                            <h4>Amount to Pay: <span id="amountDisplay" class="text-primary">&#8358;0.00</span></h4>
                        </div>

                        @if ($existingRrr)
                            <div class="alert alert-warning">
                                <strong>Existing RRR Found:</strong> {{ $existingRrr }}<br>
                                <small>You have a pending payment. Use the buttons below to complete or verify
                                    it.</small>
                            </div>
                        @endif

                        <div class="text-center">
                            <button type="button" id="payBtn" class="btn btn-primary btn-lg mr-2" disabled>
                                <i class="fas fa-money-bill-wave mr-2"></i> Pay with Remita
                            </button>
                            <button type="button" id="verifyBtn" class="btn btn-success btn-lg" style="display:none;">
                                <i class="fas fa-check-circle mr-2"></i> Verify Payment
                            </button>
                        </div>

                        <div id="loadingDiv" class="text-center mt-3" style="display:none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Initializing payment...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $remitaMode = \App\Http\Controllers\SystemSettingsController::get('remita_mode', 'demo');
    $remitaJsUrl =
        $remitaMode == 'live'
            ? 'https://login.remita.net/payment/v1/remita-pay-inline.bundle.js'
            : 'https://demo.remita.net/payment/v1/remita-pay-inline.bundle.js';
@endphp
<script src="{{ $remitaJsUrl }}"></script>

<script>
    var currentRrr = "{{ $existingRrr ?? '' }}";
    var currentAmount = {{ $existingAmount ?? 0 }};

    $('#transferType').on('change', function() {
        var type = $(this).val();
        if (type) {
            var amount = type == 'within_nigeria' ? {{ $withinNigeriaFee }} : {{ $abroadFee }};
            $('#amountDisplay').html('&#8358;' + amount.toLocaleString('en-NG', {
                minimumFractionDigits: 2
            }));
            $('#paymentAmount').show();
            $('#payBtn').prop('disabled', false);
        } else {
            $('#paymentAmount').hide();
            $('#payBtn').prop('disabled', true);
        }
    });

    @if ($existingRrr)
        $('#verifyBtn').show();
        $('#transferType').prop('disabled', true);
        $('#payBtn').text('Pay Existing RRR: {{ $existingRrr }}').prop('disabled', false);
    @endif

    $('#payBtn').on('click', function() {
        if (currentRrr) {
            makePayment(currentRrr, currentAmount);
            return;
        }

        var transferType = $('#transferType').val();
        if (!transferType) {
            swal('Error', 'Please select transfer type', 'error');
            return;
        }

        initializePayment(transferType);
    });

    $('#verifyBtn').on('click', function() {
        var rrr = currentRrr || prompt('Enter your RRR number:');
        if (rrr) {
            window.location.href = '/inter-university-transfer/verify?rrr=' + rrr;
        }
    });

    function initializePayment(transferType) {
        $('#loadingDiv').show();
        $('#payBtn').prop('disabled', true);

        $.ajax({
            url: '{{ route('inter-transfer.initialize-payment') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transfer_type: transferType
            },
            success: function(response) {
                $('#loadingDiv').hide();
                if (response.success) {
                    currentRrr = response.rrr;
                    currentAmount = response.amount;
                    $('#verifyBtn').show();
                    makePayment(response.rrr, response.amount);
                }
            },
            error: function(xhr) {
                $('#loadingDiv').hide();
                $('#payBtn').prop('disabled', false);
                var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Payment initialization failed';
                swal('Error', msg, 'error');
            }
        });
    }

    function makePayment(rrr, amount) {
        console.log('=== Payment Debug ===');
        console.log('RRR:', rrr);
        console.log('Amount:', amount);
        console.log('RmPaymentEngine available:', typeof RmPaymentEngine !== 'undefined');

        try {
            var paymentEngine = RmPaymentEngine.init({
                key: "{{ env('REMITA_PUBLIC_KEY') }}",
                processRrr: true,
                transactionId: Math.floor(Math.random() * 1101233),
                extendedData: {
                    customFields: [{
                        name: "rrr",
                        value: rrr
                    }]
                },
                onSuccess: function(response) {
                    console.log('Payment successful:', response);
                    window.location.href = '/inter-university-transfer/verify?rrr=' + rrr;
                },
                onError: function(response) {
                    console.log('Payment error:', response);
                    swal('Payment Error',
                        'Payment could not be completed. Please try again or use Verify Payment.',
                        'error');
                },
                onClose: function() {
                    console.log('Payment window closed');
                    $('#payBtn').prop('disabled', false);
                }
            });

            paymentEngine.showPaymentWidget();
        } catch (error) {
            console.error('Error initializing payment:', error);
            swal('Payment Error', 'Could not initialize payment widget. Please try again.', 'error');
            $('#payBtn').prop('disabled', false);
        }
    }
</script>
