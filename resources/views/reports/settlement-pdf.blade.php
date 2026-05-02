<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Report - {{ $boat->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-card:first-child {
            border-left: none;
        }
        .summary-card:last-child {
            border-right: none;
        }
        .summary-card .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .summary-card .value {
            font-size: 12px;
            font-weight: bold;
        }
        .summary-card .value.positive {
            color: #28a745;
        }
        .summary-card .value.negative {
            color: #dc3545;
        }
        .landing-section {
            margin-bottom: 25px;
            page-break-after: always;
        }
        .landing-section:last-child {
            page-break-after: avoid;
        }
        .landing-header {
            background: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }
        .landing-header h3 {
            font-size: 12px;
            margin-bottom: 3px;
        }
        .landing-header .date {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }
        th, td {
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0 5px 0;
            border-bottom: 1px solid #007bff;
            padding-bottom: 2px;
        }
        .totals-row {
            font-weight: bold;
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Boat Settlement Report</h1>
        <p>{{ $boat->name }} - Generated on {{ $generatedAt }}</p>
    </div>

    @foreach($reportData as $index => $data)
    <div class="landing-section{{ $index > 0 ? ' page-break' : '' }}">
        <div class="landing-header">
            <h3>Landing #{{ $data['landing']->landing_number }} - {{ $data['landing']->boat->name }}</h3>
            <div class="date">Date: {{ $data['landing']->date }}</div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="label">Gross Value</div>
                <div class="value">Rs.{{ number_format($data['summary']['gross_value'], 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Expenses</div>
                <div class="value">Rs.{{ number_format($data['summary']['total_expenses'], 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Net Payable</div>
                <div class="value">Rs.{{ number_format($data['summary']['net_owner_payable'], 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Owner Balance</div>
                <div class="value {{ $data['summary']['owner_pending'] >= 0 ? 'positive' : 'negative' }}">
                    Rs.{{ number_format($data['summary']['owner_pending'], 2) }}
                </div>
            </div>
        </div>

        @if($data['invoices']->count() > 0)
        <div class="section-title">Invoices ({{ $data['invoices']->count() }})</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Buyer</th>
                    <th class="text-right">Fish Type</th>
                    <th class="text-right">Weight (kg)</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['invoices'] as $invoice)
                <tr>
                    <td>{{ $invoice->buyer->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ $invoice->fish_type }}</td>
                    <td class="text-right">{{ number_format($invoice->weight, 2) }}</td>
                    <td class="text-right">Rs.{{ number_format($invoice->rate, 2) }}</td>
                    <td class="text-right">Rs.{{ number_format($invoice->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td colspan="4" class="text-right">Total:</td>
                    <td class="text-right">Rs.{{ number_format($data['invoices']->sum('amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
        @endif

        @if($data['expenses']->count() > 0)
        <div class="section-title">Expenses ({{ $data['expenses']->count() }})</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Vendor</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['expenses'] as $expense)
                <tr>
                    <td>{{ $expense->expense_type }}</td>
                    <td>{{ $expense->vendor ?? 'N/A' }}</td>
                    <td>{{ $expense->description ?? '-' }}</td>
                    <td class="text-right">Rs.{{ number_format($expense->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">Rs.{{ number_format($data['expenses']->sum('amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
        @endif

        @if($data['receipts']->count() > 0)
        <div class="section-title">Receipts ({{ $data['receipts']->count() }})</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Buyer</th>
                    <th>Mode</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['receipts'] as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_date }}</td>
                    <td>{{ $receipt->buyer->name ?? 'N/A' }}</td>
                    <td>{{ $receipt->payment_mode }}</td>
                    <td class="text-right">Rs.{{ number_format($receipt->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">Rs.{{ number_format($data['receipts']->sum('amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
        @endif

        @if($data['payments']->count() > 0)
        <div class="section-title">Payments ({{ $data['payments']->count() }})</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payment For</th>
                    <th>Mode</th>
                    <th>Source</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['payments'] as $payment)
                <tr>
                    <td>{{ $payment->payment_date }}</td>
                    <td>{{ $payment->payment_for }}</td>
                    <td>{{ $payment->payment_mode }}</td>
                    <td>{{ $payment->source }}</td>
                    <td class="text-right">Rs.{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td colspan="4" class="text-right">Total:</td>
                    <td class="text-right">Rs.{{ number_format($data['payments']->sum('amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
        @endif

        <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
            <table style="margin: 0; border: none;">
                <tr>
                    <td style="border: none; padding: 3px 10px; color: #333;"><strong>Gross Value:</strong></td>
                    <td style="border: none; padding: 3px 10px; text-align: right; color: #333;">Rs.{{ number_format($data['summary']['gross_value'], 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 10px; color: #333;"><strong>Less: Expenses:</strong></td>
                    <td style="border: none; padding: 3px 10px; text-align: right; color: #333;">Rs.{{ number_format($data['summary']['total_expenses'], 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 10px; color: #333;"><strong>Net Payable to Owner:</strong></td>
                    <td style="border: none; padding: 3px 10px; text-align: right; font-weight: bold; color: #333;">Rs.{{ number_format($data['summary']['net_owner_payable'], 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 10px; color: #333;"><strong>Less: Owner Payments:</strong></td>
                    <td style="border: none; padding: 3px 10px; text-align: right; color: #333;">Rs.{{ number_format($data['owner_payments']->sum('amount'), 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 10px; color: #333;"><strong>Balance Due:</strong></td>
                    <td style="border: none; padding: 3px 10px; text-align: right; font-weight: bold; color: {{ $data['summary']['owner_pending'] >= 0 ? '#dc3545' : '#28a745' }};">
                        Rs.{{ number_format($data['summary']['owner_pending'], 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>Generated by CFH Fund Management System | {{ $generatedAt }}</p>
    </div>
</body>
</html>
