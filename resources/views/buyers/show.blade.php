@extends('layouts.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('buyers.index') }}" class="text-fin-orange hover:text-fin-orange/80 mb-2 inline-block">← Back to Buyers</a>
            <h1 class="text-3xl font-bold text-off-black">{{ $buyer->name }}</h1>
            <p class="text-black-50">Phone: {{ $buyer->phone ?? 'N/A' }}</p>
            @if($buyer->address)
                <p class="text-black-50">Address: {{ $buyer->address }}</p>
            @endif
        </div>
        <button onclick="openEditBuyerModal()" class="bg-fin-orange hover:bg-fin-orange/90 text-off-black px-4 py-2 rounded-lg">
            Edit Buyer
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Total Purchased</p>
        <p class="text-xl font-bold text-off-black">₹{{ number_format($totals->total_purchased ?? 0, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Total Received</p>
        <p class="text-xl font-bold text-green-400">₹{{ number_format($totals->total_received ?? 0, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Total Pending</p>
        <p class="text-xl font-bold text-yellow-400">₹{{ number_format($totals->total_pending ?? 0, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-xs">Invoices</p>
        <p class="text-xl font-bold text-blue-400">{{ $buyer->invoices->count() }}</p>
    </div>
</div>

<div class="card rounded-xl p-6 mb-8">
    <h2 class="text-xl font-bold text-off-black mb-4">Invoices</h2>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead>
            <tr class="text-black-50 text-sm border-b border-oat-border">
                <th class="text-left pb-3">Date</th>
                <th class="text-left pb-3">Boat</th>
                <th class="text-left pb-3">Landing</th>
                <th class="text-right pb-3">Original</th>
                <th class="text-right pb-3">Received</th>
                <th class="text-right pb-3">Pending</th>
                <th class="text-center pb-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($buyer->invoices as $invoice)
            <tr class="border-b border-oat-border/50">
                <td class="py-3">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                <td class="py-3">{{ $invoice->boat->name }}</td>
                <td class="py-3">{{ $invoice->landing->date->format('Y-m-d') }}</td>
                <td class="py-3 text-right">₹{{ number_format($invoice->original_amount, 2) }}</td>
                <td class="py-3 text-right text-green-400">₹{{ number_format($invoice->received_amount, 2) }}</td>
                <td class="py-3 text-right text-yellow-400">₹{{ number_format($invoice->pending_amount, 2) }}</td>
                <td class="py-3 text-center">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($invoice->status === 'Paid') bg-green-500/20 text-green-400
                        @elseif($invoice->status === 'Partial') bg-yellow-500/20 text-yellow-400
                        @else bg-gray-500/20 text-black-50 @endif">
                        {{ $invoice->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-4 text-center text-black-50">No invoices</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl p-6">
    <h2 class="text-xl font-bold text-off-black mb-4">Payment History</h2>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead>
            <tr class="text-black-50 text-sm border-b border-oat-border">
                <th class="text-left pb-3">Date</th>
                <th class="text-left pb-3">Invoice</th>
                <th class="text-right pb-3">Amount</th>
                <th class="text-center pb-3">Mode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($buyer->receipts as $receipt)
            <tr class="border-b border-oat-border/50">
                <td class="py-3">{{ $receipt->date->format('Y-m-d') }}</td>
                <td class="py-3">{{ $receipt->invoice->invoice_date->format('Y-m-d') }}</td>
                <td class="py-3 text-right text-green-400">₹{{ number_format($receipt->amount, 2) }}</td>
                <td class="py-3 text-center">{{ $receipt->mode }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="py-4 text-center text-black-50">No receipts</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="editBuyerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Edit Buyer</h3>
            <button onclick="closeModal('editBuyerModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('buyers.update', $buyer) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Name</label>
                    <input type="text" name="name" value="{{ $buyer->name }}" required class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ $buyer->phone ?? '' }}" class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">{{ $buyer->address ?? '' }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editBuyerModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-fin-orange hover:bg-fin-orange/90 text-off-black px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openEditBuyerModal() { openModal('editBuyerModal'); }
</script>
@endsection
