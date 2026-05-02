<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .summary-box {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-label {
            display: table-cell;
            padding: 6px 12px;
            font-weight: bold;
            width: 50%;
        }
        .summary-value {
            display: table-cell;
            padding: 6px 12px;
            text-align: right;
        }
        .balance {
            background: #dcfce7;
            color: #166534;
            font-weight: bold;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        th {
            background: #e5e7eb;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background: #f3f4f6 !important;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CFH Fund Management</h1>
        <p>{{ $title }}</p>
        <p>Generated on: {{ $reportDate }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-label">Total Cash Received</div>
                <div class="summary-value">Rs.  {{ number_format($totalReceipts, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Used for Payments</div>
                <div class="summary-value">Rs.  {{ number_format($totalPayments, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Deposited to Bank</div>
                <div class="summary-value">Rs.  {{ number_format($totalDeposits, 2) }}</div>
            </div>
            <div class="summary-row balance">
                <div class="summary-label">Remaining Cash Balance</div>
                <div class="summary-value">Rs.  {{ number_format($balance, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Cash Receipts</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Buyer</th>
                    <th>Invoice Date</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                <tr>
                    <td>{{ $receipt->date->format('d M Y') }}</td>
                    <td>{{ $receipt->buyer->name ?? 'N/A' }}</td>
                    <td>{{ $receipt->landing ? $receipt->landing->date->format('d M Y') : '-' }}</td>
                    <td class="text-right">Rs.  {{ number_format($receipt->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">Rs.  {{ number_format($totalReceipts, 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Cash Payments</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Boat</th>
                    <th>Type</th>
                    <th>Vendor</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->date->format('d M Y') }}</td>
                    <td>{{ $payment->boat->name ?? '-' }}</td>
                    <td>{{ $payment->type }}</td>
                    <td>{{ $payment->vendor_name }}</td>
                    <td class="text-right">Rs.  {{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right">Total:</td>
                    <td class="text-right">Rs.  {{ number_format($totalPayments, 2) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Cash Deposits to Bank</div>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->date->format('d M Y') }}</td>
                    <td>{{ $deposit->mode }}</td>
                    <td class="text-right">Rs.  {{ number_format($deposit->amount, 2) }}</td>
                    <td>{{ $deposit->notes ?? '-' }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" class="text-right">Total:</td>
                    <td class="text-right">Rs.  {{ number_format($totalDeposits, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    <div class="footer">
        CFH Fund Management System - Confidential Report
    </div>
</body>
</html>
