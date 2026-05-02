@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('receipts.index') }}" class="text-black-50 hover:text-off-black mb-4 inline-flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Receipts
    </a>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-off-black">Receipt Details</h1>
            <p class="text-black-50 text-sm mt-1">Receipt #{{ $receipt->id }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('receipts.edit', $receipt) }}" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
                Edit
            </a>
            <form action="{{ route('receipts.destroy', $receipt) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this receipt? This will update the invoice balance.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-black-50">Date</p>
                <p class="text-off-black font-medium">{{ $receipt->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-sm text-black-50">Amount</p>
                <p class="text-2xl font-bold text-green-400">₹{{ number_format($receipt->amount, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-black-50">Buyer</p>
                <p class="text-off-black">{{ $receipt->buyer->name }}</p>
            </div>
            <div>
                <p class="text-sm text-black-50">Payment Mode</p>
                <p class="text-off-black">
                    @if($receipt->mode === 'Cash')
                        <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Cash</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-400">{{ $receipt->mode }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-black-50">Boat</p>
                <p class="text-off-black">{{ $receipt->boat->name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-black-50">Landing Date</p>
                <p class="text-off-black">{{ $receipt->landing->date->format('Y-m-d') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-black-50">Invoice Date</p>
                <p class="text-off-black">{{ $receipt->invoice->invoice_date->format('Y-m-d') }}</p>
            </div>
        </div>

        @if($receipt->notes)
        <div>
            <p class="text-sm text-black-50">Notes</p>
            <p class="text-off-black">{{ $receipt->notes }}</p>
        </div>
        @endif

        <div class="border-t border-gray-700 pt-4 mt-4">
            <h3 class="text-sm font-medium text-black-50 mb-3">Invoice Balance</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Original Amount</p>
                    <p class="text-off-black">₹{{ number_format($receipt->invoice->original_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Received Amount</p>
                    <p class="text-green-400">₹{{ number_format($receipt->invoice->received_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pending Amount</p>
                    <p class="text-yellow-400">₹{{ number_format($receipt->invoice->pending_amount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
