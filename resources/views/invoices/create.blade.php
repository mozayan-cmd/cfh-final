@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('invoices.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Invoices</a>
    <h1 class="text-3xl font-bold text-off-black">New Invoice</h1>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('invoices.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Buyer</label>
                    <select name="buyer_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Buyer</option>
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat</label>
                    <select name="boat_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Landing</label>
                <select name="landing_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                    <option value="">Select Landing</option>
                    @foreach($landings as $landing)
                        <option value="{{ $landing->id }}">{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Original Amount</label>
                    <input type="number" name="original_amount" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Create Invoice</button>
        </div>
    </form>
</div>
@endsection
