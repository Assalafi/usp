<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Payment Receipt - {{ $invoice->rrr }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
        }

        .container {
            padding: 40px;
            position: relative;
        }

        .header-section {
            text-align: center;
            width: 100%;
            border-bottom: 2px solid #000080;
            padding-bottom: 15px;
        }

        .logo-container {
            margin-bottom: 10px;
        }

        .logo {
            width: 90px;
            height: 90px;
        }

        .address-container {
            text-align: center;
        }

        .university-name {
            color: #000080;
            font-size: 26px;
            font-weight: bold;
            margin: 0;
        }

        .university-motto {
            color: #C20707;
            font-size: 16px;
            font-style: italic;
            margin: 0;
        }

        .university-contact {
            font-size: 11px;
            margin-top: 5px;
        }

        .receipt-header {
            text-align: center;
            margin: 20px 0;
        }

        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding-bottom: 5px;
        }

        .details-section {
            width: 100%;
            margin-top: 20px;
            font-size: 14px;
        }

        .details-section-container {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-top: 25px;
            overflow: hidden;
            /* To contain the border-radius */
        }

        .details-section {
            width: 100%;
            border-collapse: collapse;
            /* background-color: #f9f9f9; */
            /* Removed to allow watermark to show through */
        }

        .details-section td {
            padding: 8px 15px;
            /* Reduced vertical padding */
            border-bottom: 1px solid #e9e9e9;
        }

        .details-section tr:last-child td {
            border-bottom: none;
        }

        .details-section .label {
            font-weight: bold;
            color: #333;
        }

        .details-section .value {
            text-align: right;
            color: #444;
        }

        .items-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        .items-table thead th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .total-row td {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1000;
            opacity: 0.05;
            width: 70%;
        }

        .amount-in-words-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
        }

        .amount-in-words-section .label {
            font-weight: bold;
            color: #333;
        }

        .amount-in-words-section .value {
            font-style: italic;
            color: #555;
            text-transform: capitalize;
        }

        .cashier-section {
            float: left;
            text-align: center;
            width: 200px;
            margin-top: 37px;
        }

        .cashier-line {
            border-top: 1px solid #333;
            margin-top: 5px;
            padding-top: 5px;
            font-weight: bold;
        }

        .signature-section {
            float: right;
            text-align: center;
            width: 200px;
            margin-top: 40px;
        }

        .signature-image {
            width: 140px;
            height: auto;
            margin-bottom: -10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <img src="{{ public_path('uploads/logo.png') }}" class="watermark" alt="Watermark">

    <div class="container">
        <div class="header-section">
            <div class="logo-container">
                <img src="{{ public_path('uploads/logo.png') }}" alt="Logo" class="logo">
            </div>
            <div class="address-container">
                <p class="university-name">UNIVERSITY OF MAIDUGURI</p>
                <p class="university-motto">...Centre of Excellence</p>
                <p class="university-contact">P.M.B. 1069, Maiduguri, Borno State, Nigeria | info@unimaid.edu.ng |
                    www.unimaid.edu.ng</p>
            </div>
        </div>

        <div class="receipt-header">
            <span class="receipt-title">PAYMENT RECEIPT</span>
            {{-- the font should be like numbers --}}

            <div class="receipt-number"
                style="text-align: center; font-size: 12px; margin-top: 5px; letter-spacing: 1px; font-family: 'Courier New', monospace;">
                {{-- the digit should be padded with 0 to make it 8 digits --}}
                (RN{{ str_pad($invoice->id, 8, '0', STR_PAD_LEFT) }})
            </div>
        </div>

        <div class="details-section-container">
            <table class="details-section">
                <tr>
                    <td class="label">Billed To:</td>
                    <td class="value">{{ $invoice->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">ID Number:</td>
                    <td class="value">
                        {{ session('username') ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Program:</td>
                    <td class="value">
                        {{ DB::table('program')->where('code', $invoice->program)->value('title') ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Session:</td>
                    <td class="value">{{ $invoice->session ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Reference No (RRR):</td>
                    <td class="value">{{ $invoice->rrr }}</td>
                </tr>
                <tr>
                    <td class="label">Date Paid:</td>
                    <td class="value">{{ date('d F, Y', strtotime($invoice->updated_at)) }}</td>
                </tr>
                @if ($invoice->description == 'UNIVERSITY OF MAIDUGURI-1000127 FEES')
                    <tr>
                        <td class="label">Sponsor:</td>
                        <td class="value">
                            {{ ($invoice->fees_type ?? null) === 'nelfund' ? 'NELFUND' : 'Self Sponsor' }}</td>
                    </tr>
                @endif
            </table>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $invoice->description }}</td>
                        <td class="text-right">NGN {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>Total Paid</td>
                        <td class="text-right">NGN {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="amount-in-words-section">
            <p><span class="label">Amount in Words:</span> <span class="value">{{ $amountInWords }}</span></p>
        </div>

        {{-- name of cashier ISHIYAKU A. USMAN --}}
        <div class="cashier-section">
            <br><br>
            <p><span style="padding-top: 10px;">BAWAGANA LAWAN</span></p>
            <p class="cashier-line">Name of Cashier</p>
        </div>

        <div class="signature-section">
            <img src="{{ public_path('uploads/bursar.png') }}" alt="Signature" class="signature-image">
            <p class="signature-line">For Bursar</p>
        </div>

        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        This receipt was generated from UNIMAID portal (https://umstad.online).
    </div>
</body>

</html>
