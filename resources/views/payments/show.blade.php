@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-off-black">Payment Details</h1>
        <p class="text-black-50">View payment information</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-off-black px-4 py-2 rounded-lg">
            Back
        </a>
        <a href="{{ route('payments.edit', $payment) }}" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
            Edit
        </a>
        <button onclick="openModal('deletePaymentModal')" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">
            Delete
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black mb-6">Payment Information</h2>
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-black-50">Date</span>
                <span class="text-off-black">{{ $payment->date->format('Y-m-d') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Boat</span>
                <span class="text-off-black">{{ $payment->boat ? $payment->boat->name : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Landing</span>
                <span class="text-off-black">{{ $payment->landing ? $payment->landing->date->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Amount</span>
                <span class="text-red-400">₹{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Mode</span>
                <span class="text-off-black">{{ $payment->mode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">Source</span>
                <span class="text-off-black">{{ $payment->source }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-black-50">For</span>
                <span class="text-off-black">{{ $payment->payment_for }}</span>
            </div>
            @if($payment->payment_for === 'Loan' && $payment->loan_reference)
            <div class="flex justify-between">
                <span class="text-black-50">Loan Reference</span>
                <span class="text-off-black">{{ $payment->loan_reference }}</span>
            </div>
            @endif
            @if($payment->payment_for === 'Other' && $payment->vendor_name)
            <div class="flex justify-between">
                <span class="text-black-50">Vendor Name</span>
                <span class="text-off-black">{{ $payment->vendor_name }}</span>
            </div>
            @endif
            @if($payment->notes)
            <div class="pt-4 border-t border-gray-700">
                <span class="text-black-50">Notes</span>
                <p class="text-off-black mt-1">{{ $payment->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black mb-6">Allocations</h2>
        @if($allocations->count() > 0)
        <div class="space-y-3">
            @foreach($allocations as $allocation)
            <div class="bg-slate-50/40 dark:bg-slate-700/30 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-off-black font-medium">
                            @if($allocation->target)
                                @if(isset($allocation->target->type))
                                    {{ $allocation->target->type }}
                                @elseif(isset($allocation->target->landing_number))
                                    Landing #{{ $allocation->target->landing_number }}
                                @endif
                            @endif
                        </p>
                        <p class="text-black-50 text-sm">
                            @if(isset($allocation->target->vendor_name))
                                {{ $allocation->target->vendor_name }}
                            @endif
                        </p>
                    </div>
                    <span class="text-green-400">₹{{ number_format($allocation->amount, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500">No allocations found.</p>
        @endif
    </div>
</div>

<div id="deletePaymentModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-xl font-bold text-off-black mb-4">Delete Payment</h3>
        <p class="text-black-50 mb-6">Are you sure you want to delete this payment? This action cannot be undone and may affect related expense balances.</p>
        <form action="{{ route('payments.destroy', $payment) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('deletePaymentModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
@endsection
