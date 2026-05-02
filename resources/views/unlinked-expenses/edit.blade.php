@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-off-black">Link Expense to Landing</h1>
    <p class="text-black-50">Link an unlinked expense to a landing</p>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <div class="mb-6 pb-6 border-b border-gray-700">
        <h3 class="text-lg font-medium text-off-black mb-4">Expense Details</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-black-50 text-sm">Date</span>
                <p class="text-off-black">{{ $expense->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <span class="text-black-50 text-sm">Boat</span>
                <p class="text-off-black">{{ $expense->boat->name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-black-50 text-sm">Type</span>
                <p class="text-off-black">{{ $expense->type }}</p>
            </div>
            <div>
                <span class="text-black-50 text-sm">Amount</span>
                <p class="text-off-black font-medium">₹{{ number_format($expense->amount, 2) }}</p>
            </div>
            @if($expense->vendor_name)
            <div class="col-span-2">
                <span class="text-black-50 text-sm">Vendor</span>
                <p class="text-off-black">{{ $expense->vendor_name }}</p>
            </div>
            @endif
        </div>
    </div>

    <form action="{{ route('unlinked-expenses.update', $expense) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-6">
            <label class="block text-black-50 text-sm mb-2">Select Landing to Link</label>
            @if($landings->isEmpty())
                <p class="text-gray-500 italic">No landings found for this boat. The expense will be kept as unlinked.</p>
                <input type="hidden" name="keep_unlinked" value="1">
            @else
                <div class="space-y-2">
                    @foreach($landings as $landing)
                    <label class="flex items-center p-3 rounded-lg bg-slate-50/40 dark:bg-slate-700/30 hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="radio" name="landing_id" value="{{ $landing->id }}" class="mr-3" required>
                        <div class="flex-1">
                            <span class="text-off-black">Landing #{{ $landing->id }}</span>
                            <span class="text-black-50 ml-2">{{ $landing->date->format('Y-m-d') }}</span>
                        </div>
                        <span class="text-gray-300">Gross: ₹{{ number_format($landing->gross_value, 2) }}</span>
                    </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('unlinked-expenses.index') }}" class="text-black-50 hover:text-off-black">Cancel</a>
            <div class="flex gap-3">
                <button type="submit" name="keep_unlinked" value="1" class="px-4 py-2 text-black-50 hover:text-off-black">
                    Keep as Unlinked
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-6 py-2 rounded-lg">
                    Link to Landing
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
