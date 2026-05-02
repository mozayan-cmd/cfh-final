@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('expenses.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Expenses</a>
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-off-black">{{ $expense->type }} Expense</h1>
        <span class="px-3 py-1 rounded text-sm 
            @if($expense->payment_status === 'Paid') bg-green-500/20 text-green-400
            @elseif($expense->payment_status === 'Partial') bg-yellow-500/20 text-yellow-400
            @else bg-gray-500/20 text-black-50 @endif">
            {{ $expense->payment_status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-lg font-semibold text-off-black mb-4">Expense Details</h2>
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-black-50">Date</span>
                <span class="text-off-black">{{ $expense->date->format('Y-m-d') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Boat</span>
                <span class="text-off-black">{{ $expense->boat->name }}</span>
            </div>
            @if($expense->landing)
            <div class="flex justify-between">
                <span class="text-black-50">Landing</span>
                <a href="{{ route('landings.show', $expense->landing) }}" class="text-blue-400 hover:text-blue-300">
                    {{ $expense->landing->date->format('Y-m-d') }}
                </a>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-black-50">Vendor</span>
                <span class="text-off-black">{{ $expense->vendor_name ?? '-' }}</span>
            </div>
            @if($expense->notes)
            <div class="pt-4 border-t border-gray-700">
                <span class="text-black-50 block mb-1">Notes</span>
                <p class="text-off-black">{{ $expense->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-lg font-semibold text-off-black mb-4">Payment Summary</h2>
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-black-50">Total Amount</span>
                <span class="text-off-black font-bold text-lg">₹{{ number_format($expense->amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Amount Paid</span>
                <span class="text-green-400 font-bold">₹{{ number_format($expense->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between pt-4 border-t border-gray-700">
                <span class="text-black-50">Balance Due</span>
                <span class="text-yellow-400 font-bold text-lg">₹{{ number_format($expense->pending_amount, 2) }}</span>
            </div>
        </div>
        
        <div class="mt-6">
            <div class="w-full bg-gray-700 rounded-full h-3">
                @php
                    $progress = $expense->amount > 0 ? ($expense->paid_amount / $expense->amount) * 100 : 0;
                @endphp
                    <div class="bg-green-500 h-3 rounded-full transition-all" style="width: {{ round($progress) }}%"></div>
            </div>
            <p class="text-gray-500 text-sm mt-2 text-right">{{ round($progress) }}% paid</p>
        </div>

        @if($expense->pending_amount > 0)
        <div class="mt-6">
            <a href="{{ route('payments.create') }}?expense_id={{ $expense->id }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
                Record Payment for this Expense
            </a>
        </div>
        @endif
    </div>
</div>

<div class="card rounded-xl p-6 mt-6">
    <h2 class="text-lg font-semibold text-off-black mb-4">Payment History</h2>
    @if($expense->paymentAllocations->count() > 0)
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-black-50 text-sm">
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Payment ID</th>
                <th class="text-left px-4 py-3">Mode</th>
                <th class="text-right px-4 py-3">Amount Allocated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expense->paymentAllocations as $allocation)
            <tr class="border-t border-gray-700/50 hover:bg-white/5">
                <td class="px-4 py-3">{{ $allocation->payment->date->format('Y-m-d') }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('payments.show', $allocation->payment) }}" class="text-blue-400 hover:text-blue-300">
                        #{{ $allocation->payment->id }}
                    </a>
                </td>
                <td class="px-4 py-3">{{ $allocation->payment->mode }}</td>
                <td class="px-4 py-3 text-right text-green-400">₹{{ number_format($allocation->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-800/30">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold text-black-50">Total Paid:</td>
                <td class="px-4 py-3 text-right text-green-400 font-bold">₹{{ number_format($expense->paid_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
    @else
    <p class="text-gray-500 text-center py-8">No payments recorded for this expense yet.</p>
    @endif
</div>

<div class="flex justify-between items-center mt-6">
    <a href="{{ route('expenses.index') }}" class="text-black-50 hover:text-off-black">← Back to List</a>
    <div class="flex gap-3">
        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-400 hover:text-red-300">Delete Expense</button>
        </form>
    </div>
</div>
@endsection
