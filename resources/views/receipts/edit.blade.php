@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('receipts.show', $receipt) }}" class="text-black-50 hover:text-off-black mb-4 inline-flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Receipt
    </a>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <h1 class="text-2xl font-bold text-off-black mb-6">Edit Receipt</h1>

    <form action="{{ route('receipts.update', $receipt) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-black-50 mb-2">Date</label>
                <input type="date" name="date" value="{{ old('date', $receipt->date->format('Y-m-d')) }}" 
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none" required>
                @error('date')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-2">Amount (₹)</label>
                <input type="number" name="amount" value="{{ old('amount', $receipt->amount) }}" step="0.01" min="0.01"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none" required>
                @error('amount')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-2">Buyer</label>
                <select class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none" disabled>
                    <option>{{ $receipt->buyer->name }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Buyer cannot be changed after creation</p>
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-2">Payment Mode</label>
                <select name="mode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none" required>
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" {{ old('mode', $receipt->mode) == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
                @error('mode')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>

            <div>
                <label class="block text-sm text-black-50 mb-2">Invoice</label>
                <select class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none" disabled>
                    <option>{{ $receipt->invoice->invoice_date->format('Y-m-d') }} - ₹{{ number_format($receipt->invoice->original_amount, 2) }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Invoice cannot be changed after creation</p>
            </div>

            <div class="col-span-2">
                <label class="block text-sm text-black-50 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">{{ old('notes', $receipt->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 border-t border-gray-700 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-medium text-black-50">Invoice Balance (will be updated)</h3>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Original Amount</p>
                    <p class="text-off-black">₹{{ number_format($receipt->invoice->original_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Current Received</p>
                    <p class="text-green-400">₹{{ number_format($receipt->invoice->received_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Current Pending</p>
                    <p class="text-yellow-400">₹{{ number_format($receipt->invoice->pending_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('receipts.show', $receipt) }}" class="bg-gray-600 hover:bg-gray-700 text-off-black px-6 py-2 rounded-lg">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-6 py-2 rounded-lg">
                Update Receipt
            </button>
        </div>
    </form>
</div>
@endsection
