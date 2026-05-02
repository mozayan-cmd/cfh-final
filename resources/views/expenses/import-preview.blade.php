@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('expenses.index') }}" class="text-black-50 hover:text-off-black mb-4 inline-flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Expenses
    </a>
</div>

<div class="card rounded-xl p-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-off-black mb-6">Preview Import</h1>

    @if(!empty($errors))
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
        <h3 class="text-yellow-400 font-medium mb-2">Warnings ({{ count($errors) }})</h3>
        <ul class="text-black-50 text-sm list-disc list-inside">
            @foreach($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 mb-6">
        <p class="text-green-400">Ready to import <strong>{{ count($results) }}</strong> expense(s)</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="text-black-50">
                    <th class="text-left px-4 py-3">Line</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-left px-4 py-3">Vendor Name</th>
                    <th class="text-right px-4 py-3">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @foreach($results as $row)
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3 text-black-50">{{ $row['line'] }}</td>
                    <td class="px-4 py-3 text-off-black">{{ $row['date'] }}</td>
                    <td class="px-4 py-3 text-blue-400">{{ $row['type'] ?? 'Other' }}</td>
                    <td class="px-4 py-3 text-off-black">{{ $row['vendor_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-green-400 text-right">₹{{ number_format($row['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form action="{{ route('expenses.import.process') }}" method="POST" class="mt-6">
        @csrf
        <div class="flex justify-between items-center">
            <a href="{{ route('expenses.import') }}" class="bg-gray-600 hover:bg-gray-700 text-off-black px-6 py-2 rounded-lg">
                Cancel
            </a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-off-black px-6 py-2 rounded-lg">
                Confirm Import ({{ count($results) }})
            </button>
        </div>
    </form>
</div>
@endsection