@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('loans.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Loans</a>
    <h1 class="text-3xl font-bold text-off-black">Record New Loan</h1>
    <p class="text-black-50 mt-1">Record a loan taken from available sources</p>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('loans.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Loan Source *</label>
                    <select name="source" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($loanSources as $source)
                            <option value="{{ $source->name }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount *</label>
                    <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Mode *</label>
                    <select name="mode" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="Cash">Cash</option>
                        <option value="GP">GP</option>
                        <option value="Bank">Bank</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <input type="text" name="notes" placeholder="Optional notes about this loan" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('loans.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Record Loan</button>
        </div>
    </form>
</div>
@endsection