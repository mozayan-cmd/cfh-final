@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('boats.index') }}" class="text-fin-orange hover:text-fin-orange/80 mb-2 inline-block">← Back to Boats</a>
    <h1 class="text-3xl font-bold text-off-black">{{ $boat->name }}</h1>
    <p class="text-black-50">Owner: {{ $boat->owner_phone ?? 'N/A' }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="card rounded-xl p-6">
        <p class="text-black-50 text-sm">Total Landings</p>
        <p class="text-2xl font-bold text-off-black">{{ $boat->landings->count() }}</p>
    </div>
    <div class="card rounded-xl p-6">
        <p class="text-black-50 text-sm">Total Expenses</p>
        <p class="text-2xl font-bold text-orange-400">₹{{ number_format($landings->sum('total_expenses'), 2) }}</p>
    </div>
    <div class="card rounded-xl p-6">
        <p class="text-black-50 text-sm">Pending Settlement</p>
        <p class="text-2xl font-bold text-yellow-400">₹{{ number_format($landings->sum('owner_pending'), 2) }}</p>
    </div>
</div>

<div class="card rounded-xl p-6 mb-8">
    <h2 class="text-xl font-bold text-off-black mb-4">Landing History</h2>
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead>
            <tr class="text-black-50 text-sm border-b border-oat-border">
                <th class="text-left pb-3">Date</th>
                <th class="text-right pb-3">Gross Value</th>
                <th class="text-right pb-3">Expenses</th>
                <th class="text-right pb-3">Owner Paid</th>
                <th class="text-right pb-3">Pending</th>
                <th class="text-right pb-3">Status</th>
                <th class="text-right pb-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($landings as $landing)
            <tr class="border-b border-oat-border/50">
                <td class="py-3">{{ $landing->date->format('Y-m-d') }}</td>
                <td class="py-3 text-right">₹{{ number_format($landing->gross_value, 2) }}</td>
                <td class="py-3 text-right text-orange-400">₹{{ number_format($landing->total_expenses, 2) }}</td>
                <td class="py-3 text-right text-green-400">₹{{ number_format($landing->owner_paid, 2) }}</td>
                <td class="py-3 text-right text-yellow-400">₹{{ number_format($landing->owner_pending, 2) }}</td>
                <td class="py-3 text-right">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($landing->status === 'Settled') bg-green-500/20 text-green-400
                        @elseif($landing->status === 'Partial') bg-yellow-500/20 text-yellow-400
                        @else bg-gray-500/20 text-black-50 @endif">
                        {{ $landing->status }}
                    </span>
                </td>
                <td class="py-3 text-right">
                    <a href="{{ route('landings.show', $landing) }}" class="text-fin-orange hover:text-fin-orange/80">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-4 text-center text-gray-500">No landings for this boat</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
