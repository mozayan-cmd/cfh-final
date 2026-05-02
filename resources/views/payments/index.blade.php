@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Payments</h1>
        <p class="text-slate-500">Record owner and expense payments</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('payments.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            Export CSV
        </a>
        <a href="{{ route('payments.import') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            Import
        </a>
        <a href="{{ route('payments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + New Payment
        </a>
    </div>
</div>

@if(session('success'))
    <div class="card p-4 mb-6 border-l-4 border-green-500 text-green-700 bg-green-50">
        {{ session('success') }}
    </div>
@endif

<form id="filterForm" method="GET" action="{{ route('payments.index') }}" class="mb-6">
    <div class="card rounded-xl p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 mb-1">Filter by Boat</label>
                <select name="boat_id" id="filterBoat" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Boats</option>
                    @foreach($boats as $boat)
                        <option value="{{ $boat->id }}" {{ request('boat_id') == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-slate-600 mb-1">Filter by Landing Date</label>
                <select name="landing_id" id="filterLanding" class="w-full border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Landing Dates</option>
                    @foreach($landings as $landing)
                        <option value="{{ $landing->id }}" {{ request('landing_id') == $landing->id ? 'selected' : '' }}>{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm text-slate-600 mb-1">Payment Mode</label>
                <select name="mode" id="filterMode" class="w-full border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Modes</option>
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" {{ request('mode') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Vendor</label>
                <select name="vendor" id="filterVendor" class="w-full border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor }}" {{ request('vendor') == $vendor ? 'selected' : '' }}>{{ $vendor }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('payments.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg h-[42px] flex items-center">Clear</a>
            </div>
            <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg px-6 py-2 text-right border border-indigo-200 dark:border-indigo-700/50">
                <p class="text-xs text-indigo-600 dark:text-indigo-300">Total Amount</p>
                <p class="text-xl font-bold text-indigo-700 dark:text-indigo-200">₹{{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>
    </div>
</form>

<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 260px);">
    <div class="overflow-x-auto flex-1 min-h-0">
        <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                <th class="text-left px-6 py-4">Date</th>
                <th class="text-left px-6 py-4">Boat</th>
                <th class="text-left px-6 py-4">Landing</th>
                <th class="text-right px-6 py-4">Amount</th>
                <th class="text-center px-6 py-4">Mode</th>
                <th class="text-left px-6 py-4">Type</th>
                <th class="text-left px-6 py-4">Vendor</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            @php
                $firstAllocation = $payment->allocations->first();
                $allocationType = $firstAllocation && $firstAllocation->allocatable ? ($firstAllocation->allocatable_type === 'App\\Models\\Expense' ? $firstAllocation->allocatable->type ?? '-' : 'Landing') : '-';
                $allocationVendor = $firstAllocation && $firstAllocation->allocatable ? ($firstAllocation->allocatable_type === 'App\\Models\\Expense' ? ($firstAllocation->allocatable->vendor_name ?? '-') : '-') : '-';
            @endphp
            <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/30 {{ $payment->payment_for === 'Loan' ? 'bg-cyan-50/50 dark:bg-cyan-900/20' : '' }}">
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $payment->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $payment->boat ? $payment->boat->name : '-' }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $payment->landing ? $payment->landing->date->format('Y-m-d') : '-' }}</td>
                <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">₹{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    @if($payment->mode === 'Cash')
                        <span class="px-2 py-1 rounded text-xs bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">Cash</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">Bank</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">
                    @if($payment->payment_for === 'Loan')
                        <span class="text-cyan-600 dark:text-cyan-400">Loan</span>
                    @else
                        {{ $allocationType }}
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                    @if($payment->payment_for === 'Loan' && $payment->loan_reference)
                        {{ $payment->loan_reference }}
                    @elseif($payment->payment_for === 'Other' && $payment->vendor_name)
                        {{ $payment->vendor_name }}
                    @elseif($allocationVendor !== '-')
                        {{ $allocationVendor }}
                    @else
                        <span class="text-gray-500 dark:text-gray-400">{{ $payment->payment_for }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    <a href="{{ route('payments.edit', $payment) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-2">Edit</a>
                    <a href="{{ route('payments.show', $payment) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 mr-2">View</a>
                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No payments found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
document.getElementById('filterBoat').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterLanding').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterMode').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterVendor').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endsection