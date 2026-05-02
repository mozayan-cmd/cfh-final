@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('invoices.import') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Import</a>
    <h1 class="text-3xl font-bold text-off-black">Preview Import</h1>
    <p class="text-black-50">Review the parsed data before importing</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Boat</p>
        <p class="text-lg font-bold text-off-black">{{ $boat->name }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Landing Date</p>
        <p class="text-lg font-bold text-off-black">{{ $landing->date->format('d M Y') }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Invoice Date</p>
        <p class="text-lg font-bold text-off-black">{{ \Carbon\Carbon::parse($invoiceDate)->format('d M Y') }}</p>
    </div>
</div>

@if(count($parseErrors) > 0)
<div class="card rounded-xl p-4 mb-6 border border-red-500/30">
    <h3 class="text-red-400 font-bold mb-2">Parse Errors ({{ count($parseErrors) }})</h3>
    <div class="max-h-32 overflow-y-auto">
        @foreach($parseErrors as $error)
        <p class="text-sm text-red-300">
            Line {{ $error['line'] }}: {{ $error['content'] }} - {{ $error['error'] }}
        </p>
        @endforeach
    </div>
</div>
@endif

@if(count($parsedRows) > 0)
<form action="{{ route('invoices.process-import') }}" method="POST">
    @csrf

    <div class="card rounded-xl overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-off-black font-bold">Preview ({{ count($parsedRows) }} rows from {{ $fileName }})</h3>
                <p class="text-sm text-black-50">Review the data below and click "Import" to create invoices</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-green-400">₹{{ number_format(collect($parsedRows)->sum('amount'), 2) }}</p>
                <p class="text-xs text-black-50">Total Amount</p>
            </div>
        </div>
        
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="text-black-50 text-sm">
                    <th class="text-left px-4 py-3">Line</th>
                    <th class="text-left px-4 py-3">Buyer Name</th>
                    <th class="text-right px-4 py-3">Amount</th>
                    <th class="text-center px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parsedRows as $row)
                <tr class="border-t border-gray-700/50 {{ !$row['is_valid'] ? 'bg-red-500/10' : '' }}">
                    <td class="px-4 py-3 text-black-50">{{ $row['line'] }}</td>
                    <td class="px-4 py-3">
                        {{ $row['buyer_name'] }}
                        @if(!$row['is_valid'] || $row['warning'])
                            <span class="ml-2 text-xs text-yellow-400">({{ $row['warning'] ?? 'Will be imported anyway' }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-off-black">₹{{ number_format($row['amount'], 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if(!$row['is_valid'])
                            <span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-400">Review</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center">
        <div class="text-sm text-black-50">
            <p>Missing buyers will be created automatically</p>
            <p>Duplicate invoices (same buyer, landing, amount) will be skipped</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('invoices.import') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-off-black px-6 py-2 rounded-lg font-medium">
                Import {{ count($parsedRows) }} Invoices
            </button>
        </div>
    </div>
</form>
@else
<div class="card rounded-xl p-8 text-center">
    <p class="text-black-50 mb-4">No valid data found in the file</p>
    <a href="{{ route('invoices.import') }}" class="text-blue-400 hover:text-blue-300">Try again</a>
</div>
@endif
@endsection
