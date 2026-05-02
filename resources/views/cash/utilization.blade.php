@extends('layouts.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('dashboard') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Dashboard</a>
            <h1 class="text-3xl font-bold text-white">Cash Utilization</h1>
            <p class="text-slate-300 mt-1">Track how cash receipts are utilized and deposited</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('cash.report') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Cash Report PDF</span>
            </a>
            <a href="{{ route('cash.bank-report') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Bank Report PDF</span>
            </a>
            <a href="{{ route('cash.deposit') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <span>+ Deposit Cash</span>
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-6 gap-4 mb-8">
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Total Cash Received</p>
        <p class="text-white text-2xl font-bold">₹{{ number_format($summary['total_cash_received'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Loan Receipts (Cash)</p>
        <p class="text-purple-400 text-2xl font-bold">₹{{ number_format($summary['total_loan_receipts'] ?? 0, 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Cash Withdrawals</p>
        <p class="text-yellow-400 text-2xl font-bold">₹{{ number_format($summary['total_withdrawals'] ?? 0, 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Used for Payments</p>
        <p class="text-orange-400 text-2xl font-bold">₹{{ number_format($summary['total_utilized'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Deposited to Bank</p>
        <p class="text-blue-400 text-2xl font-bold">₹{{ number_format($summary['total_deposited'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Remaining Balance</p>
        <p class="text-2xl font-bold {{ $summary['total_balance'] > 0 ? 'text-yellow-400' : ($summary['total_balance'] < 0 ? 'text-red-400' : 'text-green-400') }}">
            ₹{{ number_format($summary['total_balance'], 2) }}
        </p>
    </div>
</div>

@if(isset($loanReceipts) && $loanReceipts->count() > 0)
<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-white font-semibold text-lg">Loan Receipts (Cash)</h3>
        <p class="text-slate-200 text-sm mt-1">Loans received via cash mode</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Source</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($loanReceipts as $receipt)
            <tr class="hover:bg-slate-700/30 transition">
                <td class="px-6 py-4 text-white">{{ $receipt->date->format('d M Y') }}</td>
                <td class="px-6 py-4 text-purple-400 font-medium">{{ $receipt->source }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded text-xs bg-purple-500/20 text-purple-400">{{ $receipt->mode }}</span>
                </td>
                <td class="px-6 py-4 text-right text-green-400 font-medium">₹{{ number_format($receipt->amount, 2) }}</td>
                <td class="px-6 py-4 text-slate-300">{{ $receipt->notes ?? '-' }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('cash.transaction.edit', $receipt) }}" class="text-blue-400 hover:text-blue-300 mr-2">Edit</a>
                    <form action="{{ route('cash.transaction.destroy', $receipt) }}" method="POST" class="inline" onsubmit="return confirm('Delete this entry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endif

@if(isset($cashWithdrawals) && $cashWithdrawals->count() > 0)
<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-white font-semibold text-lg">Cash Withdrawals from Bank</h3>
        <p class="text-slate-200 text-sm mt-1">Cash withdrawn from bank account</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Source</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($cashWithdrawals as $withdrawal)
            <tr class="hover:bg-slate-700/30 transition">
                <td class="px-6 py-4 text-white">{{ $withdrawal->date->format('d M Y') }}</td>
                <td class="px-6 py-4 text-yellow-400 font-medium">{{ $withdrawal->source }}</td>
                <td class="px-6 py-4 text-right text-green-400 font-medium">₹{{ number_format($withdrawal->amount, 2) }}</td>
                <td class="px-6 py-4 text-slate-300">{{ $withdrawal->notes ?? '-' }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('cash.transaction.edit', $withdrawal) }}" class="text-blue-400 hover:text-blue-300 mr-2">Edit</a>
                    <form action="{{ route('cash.transaction.destroy', $withdrawal) }}" method="POST" class="inline" onsubmit="return confirm('Delete this entry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endif

<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-white font-semibold text-lg">Cash Deposits to Bank</h3>
        <p class="text-slate-200 text-sm mt-1">Lumpsum cash deposits from cash receipts</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($cashDeposits as $deposit)
            <tr class="hover:bg-slate-700/30 transition">
                <td class="px-6 py-4 text-white">{{ \Carbon\Carbon::parse($deposit->date)->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-400">{{ $deposit->mode }}</span>
                </td>
                <td class="px-6 py-4 text-right text-blue-400 font-medium">₹{{ number_format($deposit->amount, 2) }}</td>
                <td class="px-6 py-4 text-slate-300">{{ $deposit->notes ?? '-' }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('cash.transaction.edit', $deposit) }}" class="text-blue-400 hover:text-blue-300 mr-2">Edit</a>
                    <form action="{{ route('cash.transaction.destroy', $deposit) }}" method="POST" class="inline" onsubmit="return confirm('Delete this entry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-slate-400">No cash deposits found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-white font-semibold text-lg">Cash Payments</h3>
        <p class="text-slate-200 text-sm mt-1">Payments made from cash</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Boat</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Landing</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Type</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Vendor</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($cashPayments as $payment)
            <tr class="hover:bg-slate-700/30 transition">
                <td class="px-6 py-4 text-white">{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                <td class="px-6 py-4 text-white">{{ $payment->boat->name ?? 'N/A' }}</td>
                <td class="px-6 py-4 text-slate-300">{{ $payment->landing ? $payment->landing->date->format('d M Y') : '-' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded text-xs {{ $payment->payment_for == 'Owner' ? 'bg-green-500/20 text-green-400' : 'bg-orange-500/20 text-orange-400' }}">
                        {{ $payment->payment_for }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-300">{{ $payment->vendor_name ?? '-' }}</td>
                <td class="px-6 py-4 text-right text-orange-400 font-medium">₹{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-slate-400">No cash payments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl overflow-hidden">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-white font-semibold text-lg">Cash Receipts</h3>
        <p class="text-slate-200 text-sm mt-1">All cash received from buyers</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">From Buyer</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Invoice Date</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($receipts as $receipt)
            <tr class="hover:bg-slate-700/30 transition">
                <td class="px-6 py-4 text-white">{{ $receipt->date->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    <div class="text-white font-medium">{{ $receipt->buyer->name ?? 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 text-slate-300">
                    @if($receipt->transaction && $receipt->transaction->invoice_id)
                        {{ \Carbon\Carbon::parse($receipt->transaction->invoice->invoice_date ?? $receipt->date)->format('d M Y') }}
                    @else
                        -
                    @endif
                </td>
                <td class="px-6 py-4 text-right text-green-400 font-medium">₹{{ number_format($receipt->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-slate-400">No cash receipts found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
