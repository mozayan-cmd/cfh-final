@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('unlinked-expenses.index') }}" class="text-blue-400 hover:text-blue-300 inline-flex items-center mb-4">
        ← Back to Unlinked Expenses
    </a>
    <h1 class="text-3xl font-bold text-off-black">Expense Details</h1>
    <p class="text-black-50">View and manage unlinked expense</p>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card rounded-xl p-6">
            <h2 class="text-xl font-semibold text-off-black mb-4">Expense Information</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-black-50 text-sm">Date</span>
                    <p class="text-off-black font-medium">{{ $expense->date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <span class="text-black-50 text-sm">Boat</span>
                    <p class="text-off-black font-medium">{{ $expense->boat->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-black-50 text-sm">Type</span>
                    <p class="text-off-black font-medium">{{ $expense->type }}</p>
                </div>
                <div>
                    <span class="text-black-50 text-sm">Vendor</span>
                    <p class="text-off-black font-medium">{{ $expense->vendor_name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-black-50 text-sm">Amount</span>
                    <p class="text-off-black font-medium text-xl">₹{{ number_format($expense->amount, 2) }}</p>
                </div>
                <div>
                    <span class="text-black-50 text-sm">Status</span>
                    <p class="font-medium">
                        @if($expense->payment_status === 'Paid')
                            <span class="px-2 py-1 text-xs rounded bg-green-900/30 text-green-400">Paid</span>
                        @elseif($expense->payment_status === 'Partial')
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900/30 text-yellow-400">Partial</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900/30 text-red-400">Pending</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($expense->notes)
                <div class="mb-6">
                    <span class="text-black-50 text-sm">Notes</span>
                    <p class="text-off-black mt-1">{{ $expense->notes }}</p>
                </div>
            @endif

            <div class="flex gap-3 pt-4 border-t border-gray-700/50">
                <a href="{{ route('unlinked-expenses.edit', $expense) }}" class="btn-primary">
                    Link to Landing
                </a>
                <form action="{{ route('unlinked-expenses.destroy', $expense) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this expense?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        Delete Expense
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card rounded-xl p-6">
            <h3 class="text-lg font-semibold text-off-black mb-4">Payment Summary</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-black-50">Total Amount:</span>
                    <span class="text-off-black font-medium">₹{{ number_format($expense->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-black-50">Paid Amount:</span>
                    <span class="text-green-400 font-medium">₹{{ number_format($expense->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-black-50">Pending Amount:</span>
                    <span class="text-red-400 font-medium">₹{{ number_format($expense->pending_amount, 2) }}</span>
                </div>
            </div>

            @if($expense->paymentAllocations->count() > 0)
                <div class="mt-6 pt-4 border-t border-gray-700/50">
                    <h4 class="text-sm font-semibold text-off-black mb-3">Payment History</h4>
                    <div class="space-y-2">
                        @foreach($expense->paymentAllocations as $allocation)
                            <div class="text-sm">
                                <div class="flex justify-between">
                                    <span class="text-black-50">Payment #{{ $allocation->payment->id }}</span>
                                    <span class="text-off-black">₹{{ number_format($allocation->amount, 2) }}</span>
                                </div>
                                <div class="text-black-50 text-xs">{{ $allocation->payment->date->format('Y-m-d') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
