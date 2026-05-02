@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-off-black mb-2">Fund Flow Report</h1>
    <p class="text-black-50">Category-wise inflows and outflows for all fund movements</p>
</div>

@if(session('success'))
    <div class="card p-4 mb-6 border-l-4 border-green-500 text-green-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="card p-4 mb-6 border-l-4 border-red-500 text-red-400">
        {{ session('error') }}
    </div>
@endif

<div class="card rounded-xl p-6 mb-6">
    <form method="GET" action="{{ route('reports.fund-flow') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-black-50 mb-2">Start Date (Optional)</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                       class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-3 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-2">End Date (Optional)</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                       class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-3 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
            </div>
        </div>
        <div class="flex gap-3 flex-wrap">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-6 py-3 rounded-lg font-medium transition-colors">
                Generate Report
            </button>
            <a href="{{ route('reports.fund-flow.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="bg-red-600 hover:bg-red-700 text-off-black px-6 py-3 rounded-lg font-medium transition-colors">
                Export PDF
            </a>
            <a href="{{ route('reports.fund-flow.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="bg-green-600 hover:bg-green-700 text-off-black px-6 py-3 rounded-lg font-medium transition-colors">
                Export Excel
            </a>
        </div>
        <p class="text-xs text-gray-500">Leave dates empty to show all-time data</p>
    </form>
</div>

@if(isset($data))
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card p-6 rounded-xl border-l-4 border-green-500">
        <h3 class="text-sm text-black-50 mb-2">Total Inflows</h3>
        <p class="text-2xl font-bold text-green-400">Rs. {{ number_format($data['summary']['total_inflows'], 2) }}</p>
    </div>
    <div class="card p-6 rounded-xl border-l-4 border-red-500">
        <h3 class="text-sm text-black-50 mb-2">Total Outflows</h3>
        <p class="text-2xl font-bold text-red-400">Rs. {{ number_format($data['summary']['total_outflows'], 2) }}</p>
    </div>
    <div class="card p-6 rounded-xl border-l-4 {{ $data['summary']['net_change'] >= 0 ? 'border-green-500' : 'border-red-500' }}">
        <h3 class="text-sm text-black-50 mb-2">Net Change</h3>
        <p class="text-2xl font-bold {{ $data['summary']['net_change'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
            Rs. {{ number_format($data['summary']['net_change'], 2) }}
        </p>
    </div>
</div>

@foreach($data['categories'] as $key => $category)
<div class="card rounded-xl p-6 mb-6">
    <details open>
        <summary class="text-xl font-bold text-off-black cursor-pointer mb-4">{{ $category['label'] }} (Total: Rs. {{ number_format($category['total'], 2) }})</summary>
        @if(count($category['transactions']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 dark:border-white/20">
                            @foreach(array_keys($category['transactions'][0]) as $header)
                                <th class="text-left py-3 px-4 text-black-50">{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category['transactions'] as $transaction)
                            <tr class="border-b border-slate-200 dark:border-white/10">
                                @foreach($transaction as $value)
                                    <td class="py-3 px-4 text-off-black">{{ is_numeric($value) ? number_format($value, 2) : $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="font-bold bg-slate-100 dark:bg-slate-800/50">
                            <td colspan="{{ count($category['transactions'][0]) - 1 }}" class="py-3 px-4 text-off-black">Total</td>
                            <td class="py-3 px-4 text-off-black">{{ number_format($category['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">No transactions found</p>
        @endif
    </details>
</div>
@endforeach
@endif
@endsection
