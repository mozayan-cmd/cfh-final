@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Receipts</h1>
        <p class="text-slate-500">Record buyer payments</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('receipts.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            Export CSV
        </a>
        <a href="{{ route('receipts.import') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            Import
        </a>
        <a href="{{ route('receipts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + New Receipt
        </a>
    </div>
</div>

<form id="filterForm" method="GET" action="{{ route('receipts.index') }}" class="mb-6">
    <div class="card rounded-xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Buyer</label>
                <select name="buyer_id" id="filterBuyer" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Buyers</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>{{ $buyer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Boat</label>
                <select name="boat_id" id="filterBoat" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Boats</option>
                    @foreach($boats as $boat)
                        <option value="{{ $boat->id }}" {{ request('boat_id') == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Landing Date</label>
                <select name="landing_id" id="filterLanding" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Landing Dates</option>
                    @foreach($landings as $landing)
                        <option value="{{ $landing->id }}" {{ request('landing_id') == $landing->id ? 'selected' : '' }}>{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Payment Mode</label>
                <select name="mode" id="filterMode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Modes</option>
                    <option value="Cash" {{ request('mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ request('mode') == 'Bank' ? 'selected' : '' }}>Bank (incl. GP)</option>
                </select>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('receipts.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg">Clear</a>
            </div>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card rounded-xl p-4 text-center">
        <p class="text-sm text-slate-600 dark:text-slate-300">Total Amount</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">₹{{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 text-center">
        <p class="text-sm text-slate-600 dark:text-slate-300">Cash</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">₹{{ number_format($totalCash, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 text-center">
        <p class="text-sm text-slate-600 dark:text-slate-300">Bank (incl. GP)</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">₹{{ number_format($totalBank, 2) }}</p>
    </div>
</div>

@if($buyerBreakdown && $buyerBreakdown->count() > 0)
<div class="mb-6">
    <h3 class="text-lg font-semibold text-slate-900 mb-3">Buyer Details for Selected Boat</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($buyerBreakdown as $breakdown)
        <div class="card rounded-xl p-4 border-l-4 border-purple-500">
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-slate-900 font-medium">{{ $breakdown->buyer->name ?? 'Unknown' }}</h4>
                <span class="text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">{{ $breakdown->receipt_count }} receipts</span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Total:</span>
                    <span class="text-slate-900 font-semibold">₹{{ number_format($breakdown->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Cash:</span>
                    <span class="text-green-600">₹{{ number_format($breakdown->cash_total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Bank/GP:</span>
                    <span class="text-blue-600">₹{{ number_format($breakdown->bank_total, 2) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 260px);">
    <div class="overflow-x-auto flex-1 min-h-0">
    <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                <th class="text-left px-6 py-4">Date</th>
                <th class="text-left px-6 py-4">Buyer</th>
                <th class="text-left px-6 py-4">Invoice</th>
                <th class="text-left px-6 py-4">Boat</th>
                <th class="text-right px-6 py-4">Amount</th>
                <th class="text-center px-6 py-4">Mode</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $receipt)
            <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $receipt->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $receipt->buyer->name }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $receipt->invoice->invoice_date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $receipt->boat->name }}</td>
                <td class="px-6 py-4 text-right text-green-600 dark:text-green-400">₹{{ number_format($receipt->amount, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    @if($receipt->mode === 'Cash')
                        <span class="px-2 py-1 rounded text-xs bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">Cash</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">Bank</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    <a href="{{ route('receipts.show', $receipt) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mx-1">View</a>
                    <a href="{{ route('receipts.edit', $receipt) }}" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 mx-1">Edit</a>
                    <form action="{{ route('receipts.destroy', $receipt) }}" method="POST" class="inline mx-1" onsubmit="return confirm('Delete this receipt? Invoice balance will be updated.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-slate-500">No receipts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
document.getElementById('filterBuyer').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterBoat').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterLanding').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterMode').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endsection
