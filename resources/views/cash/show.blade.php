@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('cash.utilization') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Cash Utilization</a>
    <h1 class="text-3xl font-bold text-off-black">Cash Receipt Details</h1>
    <p class="text-black-50 mt-1">Receipt from {{ $receipt->buyer->name ?? 'N/A' }} - ₹{{ number_format($receipt->amount, 2) }}</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-sm">Total Amount</p>
        <p class="text-2xl font-bold text-off-black">₹{{ number_format($receipt->amount, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-sm">Used for Payments</p>
        <p class="text-2xl font-bold text-orange-400">₹{{ number_format($receipt->utilized_amount, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 text-sm">Deposited to Bank</p>
        <p class="text-2xl font-bold text-blue-400">₹{{ number_format($receipt->deposited_amount, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    <div class="card rounded-xl overflow-hidden">
        <div class="bg-slate-50/40 dark:bg-white/5 px-6 py-4">
            <h2 class="text-lg font-semibold text-off-black">Cash Payments Made</h2>
        </div>
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr>
                    <th class="text-left px-6 py-3 text-black-50 text-sm font-medium">Date</th>
                    <th class="text-right px-6 py-3 text-black-50 text-sm font-medium">Amount</th>
                    <th class="text-left px-6 py-3 text-black-50 text-sm font-medium">Payment For</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($linkedPayments as $payment)
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-6 py-4 text-off-black">{{ $payment->date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-right text-orange-400 font-medium">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-4 text-black-50">
                        @if($payment->transactionable)
                            @php
                                $paymentable = $payment->transactionable;
                                $paymentFor = $paymentable->payment_for ?? 'N/A';
                            @endphp
                            {{ $paymentFor }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No cash payments made from this receipt</td>
                </tr>
                @endforelse
                @if($linkedPayments->count() > 0)
                <tr class="bg-slate-50/40 dark:bg-white/5">
                    <td class="px-6 py-3 text-off-black font-medium">Total</td>
                    <td class="px-6 py-3 text-right text-orange-400 font-bold">₹{{ number_format($linkedPayments->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="card rounded-xl overflow-hidden">
        <div class="bg-slate-50/40 dark:bg-white/5 px-6 py-4">
            <h2 class="text-lg font-semibold text-off-black">Bank Deposits</h2>
        </div>
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr>
                    <th class="text-left px-6 py-3 text-black-50 text-sm font-medium">Date</th>
                    <th class="text-right px-6 py-3 text-black-50 text-sm font-medium">Amount</th>
                    <th class="text-left px-6 py-3 text-black-50 text-sm font-medium">Mode</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($linkedDeposits as $deposit)
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-6 py-4 text-off-black">{{ $deposit->date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-right text-blue-400 font-medium">₹{{ number_format($deposit->amount, 2) }}</td>
                    <td class="px-6 py-4 text-black-50">{{ $deposit->mode }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No deposits made from this receipt</td>
                </tr>
                @endforelse
                @if($linkedDeposits->count() > 0)
                <tr class="bg-slate-50/40 dark:bg-white/5">
                    <td class="px-6 py-3 text-off-black font-medium">Total</td>
                    <td class="px-6 py-3 text-right text-blue-400 font-bold">₹{{ number_format($linkedDeposits->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 flex justify-between items-center">
    <div class="card rounded-xl p-4 flex-1 max-w-xs">
        <p class="text-black-50 text-sm">Remaining Balance</p>
        <p class="text-3xl font-bold {{ $receipt->balance > 0 ? 'text-yellow-400' : 'text-green-400' }}">
            ₹{{ number_format($receipt->balance, 2) }}
        </p>
    </div>
    @if($receipt->balance > 0)
    <a href="{{ route('cash.deposit') }}" class="bg-green-600 hover:bg-green-700 text-off-black px-4 py-2 rounded-lg">
        Deposit Remaining Cash
    </a>
    @endif
</div>
@endsection
