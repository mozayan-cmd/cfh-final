@extends('layouts.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 mb-2 inline-block">← Back to Dashboard</a>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Bank Management</h1>
            <p class="text-slate-500 dark:text-slate-300 mt-1">Track bank receipts, cash deposits, and bank payments</p>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="document.getElementById('withdrawModal').classList.remove('hidden')" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-1v1m0-1V7m0-1v1m0-1V7"></path>
                </svg>
                <span>Withdraw Cash</span>
            </button>
            <a href="{{ route('cash.bank-report') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Bank Report PDF</span>
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-6 gap-4 mb-8">
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Total Bank/GP Receipts</p>
        <p class="text-green-400 text-2xl font-bold">Rs. {{ number_format($summary['total_bank_receipts'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Loan Receipts</p>
        <p class="text-purple-400 text-2xl font-bold">Rs. {{ number_format($summary['total_loan_receipts'] ?? 0, 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Cash Deposited</p>
        <p class="text-blue-400 text-2xl font-bold">Rs. {{ number_format($summary['total_cash_deposits'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Cash Withdrawn</p>
        <p class="text-yellow-400 text-2xl font-bold">Rs. {{ number_format($summary['total_withdrawals'] ?? 0, 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Bank/GP Payments</p>
        <p class="text-orange-400 text-2xl font-bold">Rs. {{ number_format($summary['total_payments'], 2) }}</p>
    </div>
    <div class="backdrop-blur-lg bg-white/10 border-2 border-white/30 rounded-xl p-4 shadow-lg shadow-black/10">
        <p class="text-slate-200 text-sm">Remaining Bank Balance</p>
        <p class="text-2xl font-bold {{ $summary['balance'] > 0 ? 'text-yellow-400' : ($summary['balance'] < 0 ? 'text-red-400' : 'text-green-400') }}">
            Rs. {{ number_format($summary['balance'], 2) }}
        </p>
    </div>
</div>

<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-slate-800 dark:text-white font-semibold text-lg">Loan Receipts (Bank/GP)</h3>
        <p class="text-slate-200 text-sm mt-1">Loans received via bank/GP mode</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Source</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($loanReceipts as $receipt)
            <tr class="hover:bg-slate-700/30">
                <td class="px-6 py-3">
                    <span class="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs">{{ $receipt->mode }}</span>
                </td>
                <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $receipt->date->format('d M Y') }}</td>
                <td class="px-6 py-3 text-purple-400 font-medium">{{ $receipt->source }}</td>
                <td class="px-6 py-3 text-right text-green-400 font-medium">Rs. {{ number_format($receipt->amount, 2) }}</td>
                <td class="px-6 py-3 text-slate-300 text-sm">{{ $receipt->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-slate-400">No loan receipts</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-slate-800 dark:text-white font-semibold text-lg">Cash Deposits to Bank</h3>
        <p class="text-slate-200 text-sm mt-1">Cash receipts deposited to bank account</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($cashDepositsToBank as $deposit)
            <tr class="hover:bg-slate-700/30">
                <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $deposit->date->format('d M Y') }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 bg-blue-900/50 text-blue-300 rounded text-xs">{{ $deposit->mode }}</span>
                </td>
                <td class="px-6 py-3 text-right text-green-400 font-medium">Rs. {{ number_format($deposit->amount, 2) }}</td>
                <td class="px-6 py-3 text-slate-300 text-sm">{{ $deposit->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-slate-400">No cash deposits to bank</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-slate-800 dark:text-white font-semibold text-lg">Bank/GP Payments</h3>
        <p class="text-slate-200 text-sm mt-1">Payments made from bank/GP account</p>
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
            @forelse($bankPayments as $payment)
            <tr class="hover:bg-slate-700/30">
<td class="px-6 py-3 text-slate-700 dark:text-white">{{ $payment->date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $payment->boat->name ?? '-' }}</td>
                <td class="px-6 py-3 text-slate-300">{{ $payment->landing_id ? ($payment->landing ? $payment->landing->date->format('d M Y') : '-') : '-' }}</td>
                <td class="px-6 py-3">
                    @if($payment->type == 'Expense')
                        <span class="px-2 py-1 bg-orange-900/50 text-orange-300 rounded text-xs">Expense</span>
                    @else
                        <span class="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs">Owner</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-slate-300">{{ $payment->vendor_name }}</td>
                <td class="px-6 py-3 text-right text-orange-400 font-medium">Rs. {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-slate-400">No bank/GP payments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="card rounded-xl overflow-hidden">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-slate-800 dark:text-white font-semibold text-lg">Bank/GP Receipts</h3>
        <p class="text-slate-200 text-sm mt-1">Receipts received via bank/GP mode</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">From Buyer</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Boat</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Invoice Date</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($bankReceipts as $receipt)
            <tr class="hover:bg-slate-700/30">
                <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $receipt->date->format('d M Y') }}</td>
                <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $receipt->buyer->name ?? 'N/A' }}</td>
                <td class="px-6 py-3 text-slate-500 dark:text-slate-300">{{ $receipt->landing && $receipt->landing->boat ? $receipt->landing->boat->name : '-' }}</td>
                <td class="px-6 py-3">
                    @if($receipt->mode == 'GP')
                        <span class="px-2 py-1 bg-yellow-900/50 text-yellow-300 rounded text-xs">GP</span>
                    @else
                        <span class="px-2 py-1 bg-green-900/50 text-green-300 rounded text-xs">Bank</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-slate-500 dark:text-slate-300">{{ $receipt->landing ? $receipt->landing->date->format('d M Y') : '-' }}</td>
                <td class="px-6 py-3 text-right text-green-400 font-medium">Rs. {{ number_format($receipt->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-slate-400">No bank/GP receipts found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@if(isset($cashWithdrawals) && $cashWithdrawals->count() > 0)
<div class="card rounded-xl overflow-hidden mb-6">
    <div class="bg-slate-50/40 dark:bg-slate-800/30 border-b border-slate-200/40 dark:border-white/10 px-6 py-3 rounded-t-xl">
        <h3 class="text-slate-800 dark:text-white font-semibold text-lg">Cash Withdrawals</h3>
        <p class="text-slate-200 text-sm mt-1">Cash withdrawn from bank account</p>
    </div>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-700/50">
            <tr>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Date</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Mode</th>
                <th class="text-right px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Amount</th>
                <th class="text-left px-6 py-3 text-slate-100 text-sm font-medium border-b border-white/25">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-600/30">
            @forelse($cashWithdrawals as $withdrawal)
            <tr class="hover:bg-slate-700/30">
                <td class="px-6 py-3 text-slate-700 dark:text-white">{{ $withdrawal->date->format('d M Y') }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 bg-yellow-900/50 text-yellow-300 rounded text-xs">Cash</span>
                </td>
                <td class="px-6 py-3 text-right text-yellow-400 font-medium">Rs. {{ number_format($withdrawal->amount, 2) }}</td>
                <td class="px-6 py-3 text-slate-300 text-sm">{{ $withdrawal->notes ?? '-' }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endif

<div id="withdrawModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Withdraw Cash from Bank</h3>
        <form action="{{ route('bank.withdraw') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Amount *</label>
                    <input type="number" name="amount" required step="0.01" min="0.01" 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Date *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Optional notes" 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</button>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">Withdraw</button>
            </div>
        </form>
    </div>
</div>
@endsection