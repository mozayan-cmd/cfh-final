@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-800 dark:text-white mb-2">Dashboard</h1>
    <p class="text-slate-500 dark:text-slate-300">CFH Fund Management Overview</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('cash.utilization') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-500 dark:text-slate-200 text-sm">Cash in Hand</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-800 dark:text-white">₹{{ number_format($summary['cash_in_hand'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-green/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View cash details →</div>
    </a>

    <a href="{{ route('cash.bank-report') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-500 dark:text-slate-200 text-sm">Cash at Bank</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-800 dark:text-white">₹{{ number_format($summary['cash_at_bank'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-blue/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View bank details →</div>
    </a>

    <a href="{{ route('receipts.index') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-500 dark:text-slate-200 text-sm">Buyer Pending</p>
                <p class="text-2xl font-bold text-report-orange">₹{{ number_format($summary['buyer_pending'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-orange/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View buyer pending →</div>
    </a>

    <a href="{{ route('payments.index', ['payment_for' => 'Owner']) }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Boat Owner Pending</p>
                <p class="text-2xl font-bold text-report-red">₹{{ number_format($summary['boat_owner_pending'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-red/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View owner payments →</div>
    </a>

    <a href="{{ route('expenses.index', ['status' => 'pending']) }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Expense Pending</p>
                <p class="text-2xl font-bold text-report-orange">₹{{ number_format($summary['expense_pending'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-orange/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View pending expenses →</div>
    </a>

    <a href="{{ route('loans.index') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Outstanding Loans</p>
                <p class="text-2xl font-bold {{ $summary['outstanding_loans']['total'] > 0 ? 'text-report-red' : 'text-report-green' }}">
                    ₹{{ number_format($summary['outstanding_loans']['total'], 2) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    B: ₹{{ number_format($summary['outstanding_loans']['Basheer'], 0) }} |
                    P: ₹{{ number_format($summary['outstanding_loans']['Personal'], 0) }} |
                    O: ₹{{ number_format($summary['outstanding_loans']['Others'], 0) }}
                </p>
            </div>
            <div class="w-12 h-12 bg-report-red/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View loan details →</div>
    </a>

    <a href="{{ route('landings.index') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Overdue Landings</p>
                <p class="text-2xl font-bold text-report-pink">{{ $summary['overdue_landings']['count'] }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pending Expenses: ₹{{ number_format($summary['overdue_landings']['pending_expenses'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-pink/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View overdue landings →</div>
    </a>

    <a href="{{ route('unlinked-expenses.index') }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Unlinked Expenses</p>
                <p class="text-2xl font-bold text-report-orange">₹{{ number_format($summary['unlinked_expenses']['total'], 2) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $summary['unlinked_expenses']['count'] }} expenses pending linkage</p>
            </div>
            <div class="w-12 h-12 bg-report-orange/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">Click to manage →</div>
    </a>

    <a href="{{ route('payments.index', ['payment_for' => 'Loan']) }}" class="card rounded-xl p-6 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 dark:text-slate-200 text-sm">Loan Repayments</p>
                <p class="text-2xl font-bold text-report-blue">₹{{ number_format($summary['loan_payments'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-report-blue/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-report-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 text-xs text-fin-orange">View loan payments →</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Recent Landings</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-600 dark:text-slate-300 text-sm border-b border-slate-200 dark:border-white/20">
                        <th class="text-left pb-3">Date</th>
                        <th class="text-left pb-3">Boat</th>
                        <th class="text-right pb-3">Gross Value</th>
                        <th class="text-right pb-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLandings as $landing)
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <td class="py-3 text-slate-800 dark:text-white">{{ $landing['date'] }}</td>
                        <td class="py-3 text-slate-800 dark:text-white">{{ $landing['boat']['name'] ?? 'N/A' }}</td>
                        <td class="py-3 text-right text-slate-800 dark:text-white">₹{{ number_format($landing['gross_value'], 2) }}</td>
                        <td class="py-3 text-right">
                            <span class="px-2 py-1 rounded text-xs
                                @if($landing['status'] === 'Settled') bg-report-green/10 text-report-green
                                @elseif($landing['status'] === 'Partial') bg-report-orange/10 text-report-orange
                                @else bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300 @endif">
                                {{ $landing['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 dark:text-slate-400">No landings yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Recent Receipts</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-600 dark:text-slate-300 text-sm border-b border-slate-200 dark:border-white/20">
                        <th class="text-left pb-3">Date</th>
                        <th class="text-left pb-3">Buyer</th>
                        <th class="text-right pb-3">Amount</th>
                        <th class="text-right pb-3">Mode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReceipts as $receipt)
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <td class="py-3 text-slate-800 dark:text-white">{{ $receipt['date'] }}</td>
                        <td class="py-3 text-slate-800 dark:text-white">{{ $receipt['buyer_name'] }}</td>
                        <td class="py-3 text-right text-report-green">₹{{ number_format($receipt['amount'], 2) }}</td>
                        <td class="py-3 text-right text-slate-800 dark:text-white">{{ $receipt['mode'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 dark:text-slate-400">No receipts yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Recent Payments</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-600 dark:text-slate-300 text-sm border-b border-slate-200 dark:border-white/20">
                        <th class="text-left pb-3">Date</th>
                        <th class="text-left pb-3">Boat</th>
                        <th class="text-right pb-3">Amount</th>
                        <th class="text-right pb-3">For</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <td class="py-3 text-slate-800 dark:text-white">{{ $payment['date'] }}</td>
                        <td class="py-3 text-slate-800 dark:text-white">{{ $payment['boat']['name'] ?? 'N/A' }}</td>
                        <td class="py-3 text-right text-report-red">₹{{ number_format($payment['amount'], 2) }}</td>
                        <td class="py-3 text-right text-slate-800 dark:text-white">{{ $payment['payment_for'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 dark:text-slate-400">No payments yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Pending Settlements</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-600 dark:text-slate-300 text-sm border-b border-slate-200 dark:border-white/20">
                        <th class="text-left pb-3">Date</th>
                        <th class="text-left pb-3">Boat</th>
                        <th class="text-right pb-3">Pending</th>
                        <th class="text-right pb-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingSettlements as $landing)
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <td class="py-3 text-slate-800 dark:text-white">{{ $landing['date'] }}</td>
                        <td class="py-3 text-slate-800 dark:text-white">{{ $landing['boat']['name'] ?? 'N/A' }}</td>
                        <td class="py-3 text-right text-report-orange">₹{{ number_format(max(0, $landing['gross_value'] - $landing['total_expenses'] - $landing['total_owner_paid']), 2) }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('landings.show', $landing['id']) }}" class="text-fin-orange hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 dark:text-slate-400">No pending settlements</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection