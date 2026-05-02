<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fund Flow Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin-top: 20px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #f0f0f0; padding: 5px; text-align: left; font-size: 11px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 10px; }
        .summary { margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-item strong { display: block; font-size: 11px; color: #666; }
        .summary-item span { font-size: 14px; }
        .inflow { color: green; }
        .outflow { color: red; }
        .footer { margin-top: 30px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Fund Flow Report</h1>
    <p>Generated: {{ $generatedAt }}</p>
    @if($startDate && $endDate)
        <p>Date Range: {{ $startDate }} to {{ $endDate }}</p>
    @else
        <p>Date Range: All Time</p>
    @endif

    <div class="summary">
        <div class="summary-item">
            <strong>Total Inflows</strong>
            <span class="inflow">Rs. {{ number_format($data['summary']['total_inflows'], 2) }}</span>
        </div>
        <div class="summary-item">
            <strong>Total Outflows</strong>
            <span class="outflow">Rs. {{ number_format($data['summary']['total_outflows'], 2) }}</span>
        </div>
        <div class="summary-item">
            <strong>Net Change</strong>
            <span class="{{ $data['summary']['net_change'] >= 0 ? 'inflow' : 'outflow' }}">
                Rs. {{ number_format($data['summary']['net_change'], 2) }}
            </span>
        </div>
    </div>

    @foreach($data['categories'] as $key => $category)
        <h2>{{ $category['label'] }} (Total: Rs. {{ number_format($category['total'], 2) }})</h2>
        @if(count($category['transactions']) > 0)
            <table>
                <thead>
                    <tr>
                        @foreach(array_keys($category['transactions'][0]) as $header)
                            <th>{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($category['transactions'] as $transaction)
                        <tr>
                            @foreach($transaction as $value)
                                <td>{{ is_numeric($value) ? number_format($value, 2) : $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="{{ count($category['transactions'][0]) - 1 }}"><strong>Total</strong></td>
                        <td><strong>{{ number_format($category['total'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @else
            <p>No transactions found</p>
        @endif
    @endforeach

    <div class="footer">
        <p>CFH Fund Management - Generated on {{ $generatedAt }}</p>
    </div>
</body>
</html>