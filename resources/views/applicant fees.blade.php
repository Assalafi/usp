@php
    use Illuminate\Support\Facades\DB;

    // Get applicant data
    $applicant = DB::table('applicants')->where('user_id', session('id'))->first();

    // Check for existing invoice
    $invs = DB::table('invoices')
        ->where(['username' => session('id'), 'description' => 'POST UTME', 'status' => 'Pending'])
        ->orderBy('id', 'ASC')
        ->get();

    //dd($invs);

    // If no invoice exists, create one automatically
    if ($invs->isEmpty() && $applicant) {
        $amount = 2000;
        $description = 'POST UTME';
        $serviceTypeId = 4430731;
        $orderId = 'POSTUTME' . time() . rand(100, 999);

        $phone = $applicant->phone ?? '';
        $email = $applicant->email ?? '';

        if (!empty($phone) && !empty($email)) {
            $baseUrl = env('REMITA_BASE_URL') . 'remita/exapp/api/v1/send/api';
            $merchantId = env('REMITA_MERCHANT_ID', 2547916);
            $apiKey = env('REMITA_API_KEY', 1946);

            $hash = hash('sha512', $merchantId . $serviceTypeId . $orderId . $amount . $apiKey);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $baseUrl . '/echannelsvc/merchant/api/paymentinit',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 15, // Set 15-second timeout
                CURLOPT_CONNECTTIMEOUT => 10, // Set 10-second connection timeout
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'serviceTypeId' => $serviceTypeId,
                    'amount' => $amount,
                    'orderId' => $orderId,
                    'payerName' => trim(($applicant->surname ?? '') . ' ' . ($applicant->firstName ?? '')),
                    'payerEmail' => $email,
                    'payerPhone' => $phone,
                    'description' => $description,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $hash,
                ],
            ]);

            $response = curl_exec($curl);
            $curl_error = curl_error($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // Check for cURL errors (including timeouts)
            if ($response === false || !empty($curl_error)) {
                // Log the error for debugging but continue silently
                error_log('Invoice generation cURL error: ' . $curl_error);
                // Don't create invoice, let the user try the manual form
        } elseif ($http_code !== 200) {
            // Log HTTP error
            error_log('Invoice generation HTTP error: ' . $http_code);
            // Don't create invoice, let the user try the manual form
            } else {
                $substr = substr($response, 7, -1);
                $obj = json_decode($substr, true);

                if (isset($obj['RRR']) && !empty($obj['RRR'])) {
                    $datas = [
                        'username' => session('id'),
                        'description' => $description,
                        'amount' => $amount,
                        'orderId' => $orderId,
                        'serviceTypeId' => $serviceTypeId,
                        'rrr' => $obj['RRR'],
                        'phone' => $phone,
                        'email' => $email,
                        'status' => 'Pending',
                    ];

                    try {
                        DB::table('invoices')->insert($datas);
                        // Refresh invoice list
                        $invs = DB::table('invoices')
                            ->where(['username' => session('id'), 'description' => 'POST UTME', 'status' => 'Pending'])
                            ->orderBy('id', 'ASC')
                            ->get();
                    } catch (Exception $e) {
                        // Handle error silently for now
                    }
                }
            } // Close the else block for successful cURL response
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'UNIMAID') }} | POST-UTME Payment</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1rem 0;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 1rem 0;
        }

        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .content {
            padding: 2rem 1.5rem;
        }

        .applicant-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #667eea;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: #6b7280;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: right;
        }

        .amount-display {
            text-align: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .amount-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .amount-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .security-note {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            font-size: 0.85rem;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .footer-links {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 0 1rem;
        }

        .footer-links a:hover {
            color: #667eea;
        }

        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Mobile Optimizations */
        @media (max-width: 576px) {
            body {
                padding: 0.5rem 0;
            }

            .container {
                padding: 0 0.5rem;
            }

            .content {
                padding: 1.5rem 1rem;
            }

            .header {
                padding: 1.5rem 1rem 1rem;
            }

            .header h1 {
                font-size: 1.25rem;
            }

            .amount-value {
                font-size: 2rem;
            }

            .detail-row {
                flex-direction: column;
                gap: 0.25rem;
            }

            .detail-value {
                text-align: left;
                color: #667eea;
                font-weight: 700;
            }

            .applicant-details {
                padding: 1rem;
            }

            .footer-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .footer-links a {
                display: block;
                margin: 0.25rem 0;
            }
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: none;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom: none;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        /* University Header Card */
        .university-header-card {
            margin-bottom: 2rem;
        }

        .university-header-card .card {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .university-header-card .display-6 {
                font-size: 1.5rem;
            }

            .university-header-card img {
                width: 60px !important;
                height: 60px !important;
            }
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="payment-card">
            <!-- University Header with Logo -->
            <div class="university-header-card">
                <div class="card shadow-lg rounded-4 border-0 overflow-hidden bg-white position-relative"
                    style="background: linear-gradient(135deg, #f8fafc 60%, #e7e9fb 100%);">
                    <div class="d-flex flex-column flex-md-row align-items-center p-2 p-md-4 gap-1 gap-md-3">
                        <div class="flex-shrink-0 text-center mb-1 mb-md-0">
                            <img src="{{ url('uploads/logo.png') }}" alt="UNIMAID Logo"
                                class="rounded-circle shadow bg-white"
                                style="width: 80px; height: 80px; border: 3px solid #fff;">
                        </div>
                        <div class="flex-grow-1 text-center text-md-start">
                            <h3 class="display-6 fw-bold mb-1 text-center" style="color:#4e54c8;letter-spacing:0.01em;">
                                UNIMAID POST-UTME FEE PAYMENT</h3>
                            <p class="text-muted mb-0 text-center">Secure Online Payment Portal for Application Fee</p>
                        </div>
                        <div class="flex-shrink-0 align-self-md-start align-self-center mt-1 mt-md-0">
                            <a href="/logout"
                                class="btn btn-outline-danger btn-sm px-3 py-1 rounded-pill fw-semibold d-inline-flex align-items-center">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </div>
                    </div>
                    <!-- SVG Decorative Wave -->
                    <div style="height:25px;overflow:hidden;line-height:0;">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none"
                            style="height:100%;width:100%;fill:#667eea;opacity:0.8;">
                            <path
                                d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <!-- Applicant Details -->
                        <div class="applicant-details">
                            <div class="detail-row">
                                <span class="detail-label">JAMB Number</span>
                                <span class="detail-value">{{ $applicant->username }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Full Name</span>
                                <span class="detail-value">{{ $applicant->surname ?? '' }}
                                    {{ $applicant->first_name ?? '' }}
                                    {{ $applicant->other_name ?? '' }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Program</span>
                                <span class="detail-value">{{ $applicant->program ?? 'N/A' }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Faculty</span>
                                <span class="detail-value">{{ $applicant->faculty ?? 'N/A' }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Session</span>
                                <span class="detail-value">{{ $applicant->session ?? '2025/2026' }}</span>
                            </div>
                            {{-- amount --}}
                            <div class="detail-row">
                                <span class="detail-label">Amount</span>
                                <span class="detail-value">₦{{ number_format(2000, 0) }}</span>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <!-- Payment Section -->
                        @forelse ($invs as $inv)
                            <div class="security-note">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure payment powered by Remita</span>
                            </div>

                            {{-- Display RRR --}}
                            <div class="detail-row">
                                <span class="detail-label">RRR</span>
                                <span class="detail-value">{{ $inv->rrr ?? 'N/A' }}</span>
                            </div>

                            <button type="button" class="btn btn-primary" id="payButton" onclick="makePayment()">
                                <i class="fas fa-credit-card me-2"></i>
                                Pay ₦{{ number_format(2000) }} Now
                            </button>

                            <script>
                                function makePayment() {
                                    const payBtn = document.getElementById('payButton');
                                    const rrrValue = '{{ $inv->rrr ?? '' }}';

                                    // Validate RRR exists
                                    if (!rrrValue || rrrValue.trim() === '') {
                                        alert('Payment reference (RRR) is missing. Please refresh the page and try again.');
                                        return;
                                    }

                                    payBtn.disabled = true;
                                    payBtn.innerHTML =
                                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';


                                    // Start the retry mechanism
                                    waitForRemita();

                                    // Function to dynamically load Remita script if needed
                                    function loadRemitaScript() {
                                        console.log('Attempting to reload Remita script...');

                                        // Remove existing script if any
                                        const existingScript = document.querySelector('script[src*="remita-pay-inline"]');
                                        if (existingScript) {
                                            existingScript.remove();
                                        }

                                        // Create new script element
                                        const script = document.createElement('script');
                                        script.type = 'text/javascript';
                                        script.src = '{{ env('REMITA_BASE_URL') }}payment/v1/remita-pay-inline.bundle.js';

                                        script.onload = function() {
                                            console.log('Remita script reloaded successfully');
                                            setTimeout(() => {
                                                if (typeof RmPaymentEngine !== 'undefined') {
                                                    showStatusMessage('Payment system ready! You can try again.', 'success');
                                                } else {
                                                    showStatusMessage(
                                                        'Payment system still loading. Please try alternative payment method.',
                                                        'error');
                                                }
                                            }, 1000);
                                        };

                                        script.onerror = function() {
                                            console.error('Failed to reload Remita script');
                                            showStatusMessage(
                                                'Unable to load payment system. Please check your internet connection and try again.',
                                                'error');
                                        };

                                        // Append to document head
                                        document.head.appendChild(script);
                                    }

                                    // Wait for Remita script to load with simple retry
                                    function waitForRemita(attempts = 0) {
                                        console.log('Checking for RmPaymentEngine, attempt:', attempts + 1);
                                        if (typeof RmPaymentEngine !== 'undefined') {
                                            // Script loaded, proceed with payment
                                            initiatePayment();
                                            return;
                                        }

                                        if (attempts >= 10) {
                                            // Give up after 10 seconds
                                            console.error('Remita Payment Engine failed to load after 10 seconds');
                                            payBtn.disabled = false;
                                            payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i> Pay ₦{{ number_format(2000) }} Now';

                                            // Try to reload the script as last resort
                                            showStatusMessage('Payment system is loading slowly. Trying to reload...', 'warning');
                                            loadRemitaScript();
                                            return;
                                        }

                                        // Try again in 0.5 seconds for more responsive checking
                                        setTimeout(() => waitForRemita(attempts + 1), 500);
                                    }

                                    function initiatePayment() {

                                        try {
                                            // Flag to track payment success
                                            let paymentSuccessful = false;

                                            console.log('Initializing payment engine...');
                                            const paymentEngine = RmPaymentEngine.init({
                                                key: "{{ env('REMITA_PUBLIC_KEY') }}",
                                                processRrr: true,
                                                transactionId: Math.floor(Math.random() * 1101233),
                                                extendedData: {
                                                    customFields: [{
                                                        name: "rrr",
                                                        value: rrrValue
                                                    }]
                                                },
                                                onSuccess: function(response) {
                                                    console.log('=== Payment Success Callback ===');
                                                    console.log('Payment Success:', response);
                                                    paymentSuccessful = true; // Mark payment as successful
                                                    payBtn.innerHTML = '<i class="fas fa-check me-2"></i> Payment Successful!';

                                                    // Show success message on page
                                                    showStatusMessage('Payment successful! Redirecting to application form...',
                                                        'success');

                                                    setTimeout(() => {
                                                        // Redirect to verify route which will then redirect to dashboard
                                                        window.location.href = '/verify/' + rrrValue;
                                                    }, 2000);
                                                },
                                                onError: function(response) {
                                                    console.error('Payment Failed:', response);
                                                    payBtn.disabled = false;
                                                    payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i> Try Again';

                                                    // More detailed error handling
                                                    let errorMsg = 'Payment failed. Please try again.';
                                                    if (response && response.message) {
                                                        errorMsg = response.message;
                                                    } else if (response && response.error) {
                                                        errorMsg = response.error;
                                                    } else if (response && response.statusMessage) {
                                                        errorMsg = response.statusMessage;
                                                    }

                                                    console.log('Detailed error:', errorMsg);

                                                    // Show error message on page
                                                    showStatusMessage('Payment failed: ' + errorMsg, 'error');
                                                },
                                                onClose: function() {
                                                    console.log('Payment widget closed by user');

                                                    // Only show cancellation message if payment wasn't successful
                                                    if (!paymentSuccessful) {
                                                        payBtn.disabled = false;
                                                        payBtn.innerHTML =
                                                            '<i class="fas fa-credit-card me-2"></i> Pay ₦{{ number_format(2000) }} Now';

                                                        // Show cancellation message on page
                                                        showStatusMessage('Payment cancelled. You can try again when ready.',
                                                            'warning');
                                                    }
                                                }
                                            });

                                            console.log('Displaying payment widget...');
                                            console.log('Payment engine object:', paymentEngine);
                                            paymentEngine.showPaymentWidget();
                                            console.log('showPaymentWidget() called successfully');
                                        } catch (error) {
                                            console.error('Payment initialization failed:', error);
                                            payBtn.disabled = false;
                                            payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i> Pay ₦{{ number_format(2000) }} Now';
                                            alert('Payment system error: ' + error.message);
                                        }
                                    } // End of initiatePayment function


                                    // Function to show status messages on the page
                                    function showStatusMessage(message, type) {
                                        // Remove any existing status messages
                                        const existingMsg = document.querySelector('.payment-status-message');
                                        if (existingMsg) {
                                            existingMsg.remove();
                                        }

                                        // Create new status message
                                        const statusDiv = document.createElement('div');
                                        statusDiv.className = 'payment-status-message alert';

                                        // Set appropriate alert class based on type
                                        switch (type) {
                                            case 'success':
                                                statusDiv.className += ' alert-success';
                                                statusDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + message;
                                                break;
                                            case 'error':
                                                statusDiv.className += ' alert-danger';
                                                statusDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + message;
                                                break;
                                            case 'warning':
                                                statusDiv.className += ' alert-warning';
                                                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message;
                                                break;
                                            default:
                                                statusDiv.className += ' alert-info';
                                                statusDiv.innerHTML = '<i class="fas fa-info-circle me-2"></i>' + message;
                                        }

                                        // Insert the message before the payment button
                                        const payButton = document.getElementById('payButton');
                                        if (payButton) {
                                            payButton.parentNode.insertBefore(statusDiv, payButton);
                                        }

                                        // Auto-remove success messages after 5 seconds
                                        if (type === 'success') {
                                            setTimeout(() => {
                                                if (statusDiv.parentNode) {
                                                    statusDiv.remove();
                                                }
                                            }, 5000);
                                        }
                                    }
                                } // End of makePayment function
                            </script>
                        @empty

                            <div class="card shadow-sm border-0 rounded-4 mb-4 mt-2"
                                style="max-width: 500px; margin: 0 auto;">
                                <div class="card-body p-4">
                                    <form action="invoices-applicant-fees" method="get" enctype="multipart/form-data"
                                        autocomplete="off">
                                        @csrf
                                        <div class="mb-3 position-relative">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bg-white border-end-0"><i
                                                        class="fas fa-phone-alt text-primary"></i></span>
                                                <input id="phone" type="tel" class="form-control rounded-end"
                                                    name="phone" required placeholder="e.g. 08012345678"
                                                    style="padding-left:2.5rem;">
                                            </div>
                                        </div>
                                        <div class="mb-3 position-relative">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bg-white border-end-0"><i
                                                        class="fas fa-envelope text-primary"></i></span>
                                                <input id="email" type="email" class="form-control rounded-end"
                                                    name="email" required placeholder="e.g. you@email.com"
                                                    style="padding-left:2.5rem;">
                                            </div>
                                        </div>
                                        <button type="submit" id="submitButton"
                                            class="btn btn-primary btn-lg w-100 shadow fw-semibold mt-2 mb-2">
                                            <i class="fas fa-file-invoice me-2"></i>Generate Invoice
                                        </button>
                                        <div class="form-text text-center text-muted mt-2" style="font-size:0.95em;">
                                            <i class="fas fa-lock me-1"></i>Your contact details are kept private and
                                            used only
                                            for payment processing.
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>

                <div class="divider">
                    <span>or</span>
                </div>

                <!-- Verify Payment -->
                <button class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#verifyModal">
                    <i class="fas fa-search me-2"></i>
                    Verify Payment Status
                </button>



                <!-- Footer Links -->
                <div class="footer-links">
                    <a href="/"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
                    <a href="#"><i class="fas fa-headset me-1"></i>Support</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner"></div>
            <p style="color: #667eea; margin-top: 1rem;">Processing your request...</p>
        </div>
    </div>

    <!-- Verify Payment Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyModalLabel">
                        <i class="fas fa-search me-2"></i>
                        Verify Payment Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="verify" method="GET" id="verifyForm" autocomplete="off">
                        @csrf
                        <div class="mb-4">
                            <label for="rrr" class="form-label fw-semibold text-primary">
                                <i class="fas fa-receipt me-2"></i>RRR Number
                            </label>
                            <div class="input-group input-group-lg rounded-3 shadow-sm">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="fas fa-hashtag text-primary"></i></span>
                                <input type="text" class="form-control border-start-0 rounded-end" name="rrr"
                                    id="rrr" placeholder="Enter your RRR number" required pattern="[0-9]+"
                                    title="Please enter a valid RRR number">
                            </div>
                            <div class="form-text mt-2 text-muted text-center" style="font-size:0.97em;">
                                <i class="fas fa-info-circle me-1"></i>
                                Enter the RRR number from your payment receipt. This is required to verify your payment
                                status.
                            </div>
                        </div>
                        <div class="d-grid gap-2 mb-2">
                            <button type="submit"
                                class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm w-100">
                                <i class="fas fa-search me-2"></i>Verify Payment
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill w-100"
                                data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Remita Payment Widget -->
    <script type="text/javascript" src="{{ env('REMITA_BASE_URL') }}payment/v1/remita-pay-inline.bundle.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            const verifyForm = document.getElementById('verifyForm');

            // Show loading function
            function showLoading() {
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'flex';
                }
            }

            // Payment button click handled by Remita widget
            const payButton = document.getElementById('payButton');
            if (payButton) {
                payButton.addEventListener('click', function() {
                    showLoading();
                    // Loading will be hidden when widget opens
                    setTimeout(() => {
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'none';
                        }
                    }, 1000);
                });
            }

            // Verify form handling
            if (verifyForm) {
                verifyForm.addEventListener('submit', function(e) {
                    const rrr = document.querySelector('input[name="rrr"]').value.trim();

                    if (!/^[0-9]+$/.test(rrr) || rrr.length < 10) {
                        e.preventDefault();
                        alert('Please enter a valid RRR number');
                        return;
                    }

                    // Show loading and set timeout warning
                    showLoading();

                    // Show warning after 10 seconds
                    setTimeout(() => {
                        const loadingOverlay = document.getElementById('loadingOverlay');
                        if (loadingOverlay && loadingOverlay.style.display !== 'none') {
                            const loadingContent = loadingOverlay.querySelector('.spinner')
                                .parentNode;
                            if (loadingContent) {
                                loadingContent.innerHTML = `
                                    <div class="spinner"></div>
                                    <div style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                                        <p><strong>Payment verification is taking longer than usual...</strong></p>
                                        <p>This may be due to high traffic on the payment gateway.</p>
                                        <p>Please wait or try again later.</p>
                                    </div>
                                `;
                            }
                        }
                    }, 15000);
                });
            }

            // Auto-format RRR input
            const rrrInput = document.querySelector('input[name="rrr"]');
            if (rrrInput) {
                rrrInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                });
            }
        });
    </script>
    @if (session('success'))
        <script>
            swal("", "{{ session('success') }}", "success");
        </script>
    @endif
    @if (session('info'))
        <script>
            swal("", "{{ session('info') }}", "info");
        </script>
    @endif
    @if (session('error'))
        <script>
            swal("Oops!!!", "{{ session('error') }}", "error");
        </script>
    @endif
</body>

</html>
