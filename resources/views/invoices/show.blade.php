@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('invoices.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Invoices</a>
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-off-black">Invoice #{{ $invoice->id }}</h1>
            <p class="text-black-50 mt-1">Invoice Details</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openEditModal()" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Edit Invoice</button>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">Delete</button>
            </form>
        </div>
    </div>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h3 class="text-xl font-bold text-off-black mb-4">Invoice Details</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-black-50">Status</span>
                <span class="px-2 py-1 rounded text-sm 
                    @if($invoice->status === 'Paid') bg-green-500/20 text-green-400
                    @elseif($invoice->status === 'Partial') bg-yellow-500/20 text-yellow-400
                    @else bg-gray-500/20 text-black-50 @endif">
                    {{ $invoice->status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Invoice Date</span>
                <span class="text-off-black">{{ $invoice->invoice_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Original Amount</span>
                <span class="text-off-black font-medium">₹{{ number_format($invoice->original_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Received Amount</span>
                <span class="text-green-400">₹{{ number_format($invoice->received_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Pending Amount</span>
                <span class="text-yellow-400">₹{{ number_format($invoice->pending_amount, 2) }}</span>
            </div>
            @if($invoice->notes)
            <div class="pt-3 border-t border-gray-700">
                <span class="text-black-50">Notes</span>
                <p class="text-off-black mt-1">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h3 class="text-xl font-bold text-off-black mb-4">Related Information</h3>
        <div class="space-y-3">
            <div>
                <span class="text-black-50">Buyer</span>
                <p class="text-off-black">{{ $invoice->buyer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-black-50">Boat</span>
                <p class="text-off-black">{{ $invoice->boat->name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-black-50">Landing</span>
                <p class="text-off-black">
                    @if($invoice->landing)
                        {{ $invoice->landing->date->format('d M Y') }} - ₹{{ number_format($invoice->landing->gross_value, 2) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 card rounded-xl p-6">
    <h3 class="text-xl font-bold text-off-black mb-4">Receipts</h3>
    @if($invoice->receipts->isEmpty())
        <p class="text-gray-500 text-center py-4">No receipts found for this invoice.</p>
    @else
        <div class="overflow-x-auto table-container">
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="text-black-50 text-sm">
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Mode</th>
                    <th class="text-right px-4 py-3">Amount</th>
                    <th class="text-left px-4 py-3">Source</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->receipts as $receipt)
                <tr class="border-t border-gray-700/50">
                    <td class="px-4 py-3 text-off-black">{{ $receipt->date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-off-black">{{ $receipt->mode }}</td>
                    <td class="px-4 py-3 text-right text-green-400">₹{{ number_format($receipt->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Edit Invoice</h3>
            <button onclick="closeEditModal()" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('invoices.update', $invoice) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Original Amount</label>
                    <input type="number" name="original_amount" value="{{ $invoice->original_amount }}" required step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">{{ $invoice->notes }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection