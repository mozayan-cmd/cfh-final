@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('cash.utilization') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Cash Utilization</a>
    <h1 class="text-3xl font-bold text-off-black">Edit Transaction</h1>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('cash.transaction.update', $transaction) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date *</label>
                    <input type="date" name="date" required value="{{ $transaction->date->format('Y-m-d') }}" class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount *</label>
                    <input type="number" name="amount" required step="0.01" min="0.01" value="{{ $transaction->amount }}" class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ $transaction->notes }}" class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('cash.utilization') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update</button>
        </div>
    </form>
</div>
@endsection