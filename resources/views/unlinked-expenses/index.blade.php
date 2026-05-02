@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-off-black">Unlinked Expenses</h1>
    <p class="text-black-50">Manage expenses that are not yet linked to a landing</p>
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

@if($unlinkedExpenses->isEmpty())
    <div class="card rounded-xl p-8 text-center">
        <p class="text-black-50">No unlinked expenses found. All expenses are linked to landings.</p>
    </div>
@else
    <div class="card rounded-xl overflow-hidden">
        <div class="overflow-x-auto table-container">
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="text-black-50 text-sm">
                    <th class="text-left px-6 py-4">Date</th>
                    <th class="text-left px-6 py-4">Boat</th>
                    <th class="text-left px-6 py-4">Type</th>
                    <th class="text-right px-6 py-4">Amount</th>
                    <th class="text-left px-6 py-4">Vendor</th>
                    <th class="text-center px-6 py-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unlinkedExpenses as $expense)
                <tr class="border-t border-gray-700/50 hover:bg-white/5">
                    <td class="px-6 py-4 text-off-black">{{ $expense->date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-off-black">{{ $expense->boat->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-off-black">{{ $expense->type }}</td>
                    <td class="px-6 py-4 text-right text-off-black font-medium">₹{{ number_format($expense->amount, 2) }}</td>
                    <td class="px-6 py-4 text-off-black">{{ $expense->vendor_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('unlinked-expenses.show', $expense) }}" class="text-green-400 hover:text-green-300">View</a>
                            <a href="{{ route('unlinked-expenses.edit', $expense) }}" class="text-blue-400 hover:text-blue-300">Link</a>
                            <form action="{{ route('unlinked-expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    
    <div class="mt-4 card p-4">
        <div class="flex justify-between items-center">
            <span class="text-black-50">Total Unlinked Expenses:</span>
            <span class="text-xl font-bold text-off-black">₹{{ number_format($unlinkedExpenses->sum('amount'), 2) }}</span>
        </div>
    </div>
@endif
@endsection
