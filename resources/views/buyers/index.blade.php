@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Buyers</h1>
    <p class="text-slate-500">Manage buyer accounts</p>
</div>

<div class="card rounded-xl p-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-cyan-50 dark:bg-cyan-900/30 rounded-lg p-3 text-center border border-cyan-200 dark:border-cyan-700/50">
            <p class="text-xs sm:text-sm text-cyan-700 dark:text-cyan-300">Total Purchased</p>
            <p class="text-lg sm:text-xl font-bold text-cyan-800 dark:text-cyan-200">₹{{ number_format($totalPurchased, 2) }}</p>
        </div>
        <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center border border-green-200 dark:border-green-700/50">
            <p class="text-xs sm:text-sm text-green-700 dark:text-green-300">Total Received</p>
            <p class="text-lg sm:text-xl font-bold text-green-800 dark:text-green-200">₹{{ number_format($totalReceived, 2) }}</p>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-3 text-center border border-yellow-200 dark:border-yellow-700/50">
            <p class="text-xs sm:text-sm text-yellow-700 dark:text-yellow-300">Pending Amount</p>
            <p class="text-lg sm:text-xl font-bold text-yellow-800 dark:text-yellow-200">₹{{ number_format($totalPending, 2) }}</p>
        </div>
    </div>

    @if($buyers->isEmpty())
        <div class="text-center py-8">
            <p class="text-slate-500 mb-4">No buyers found. Add your first buyer to get started.</p>
            <a href="{{ route('buyers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                + Add Buyer
            </a>
        </div>
    @else
    <div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 340px);">
        <div class="overflow-x-auto flex-1 min-h-0">
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                    <th class="text-left px-6 py-4">
                        <a href="{{ route('buyers.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-slate-700 dark:hover:text-white">
                            Name
                            @if(request('sort') == 'name')
                                <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="text-left px-6 py-4">Contact</th>
                    <th class="text-right px-6 py-4">
                        <a href="{{ route('buyers.index', array_merge(request()->query(), ['sort' => 'purchased', 'direction' => request('sort') == 'purchased' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end gap-1 hover:text-slate-700 dark:hover:text-white">
                            Total Purchased
                            @if(request('sort') == 'purchased')
                                <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="text-right px-6 py-4">
                        <a href="{{ route('buyers.index', array_merge(request()->query(), ['sort' => 'received', 'direction' => request('sort') == 'received' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end gap-1 hover:text-slate-700 dark:hover:text-white">
                            Total Received
                            @if(request('sort') == 'received')
                                <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="text-right px-6 py-4">
                        <a href="{{ route('buyers.index', array_merge(request()->query(), ['sort' => 'pending', 'direction' => request('sort') == 'pending' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end gap-1 hover:text-slate-700 dark:hover:text-white">
                            Pending
                            @if(request('sort') == 'pending')
                                <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="text-center px-6 py-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($buyers as $buyer)
                <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('buyers.show', $buyer) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                            {{ $buyer->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                        {{ $buyer->phone ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-300">
                        ₹{{ number_format($buyer->total_purchased, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-300">
                        ₹{{ number_format($buyer->total_received, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right {{ $buyer->total_pending > 0 ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-slate-600 dark:text-slate-300' }}">
                        ₹{{ number_format($buyer->total_pending, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <a href="{{ route('buyers.show', $buyer) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                            View
                        </a>
                        <form action="{{ route('buyers.destroy', $buyer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this buyer?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>

<div class="card rounded-xl p-4 mt-6">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Add New Buyer</h2>
    <form action="{{ route('buyers.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-slate-300 dark:border-white/20 bg-white/60 dark:bg-slate-700/50 rounded-lg px-4 py-2 text-slate-900 dark:text-white focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none" placeholder="Buyer name">
        </div>
        <div>
            <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Phone (optional)</label>
            <input type="text" name="phone" class="w-full border border-slate-300 dark:border-white/20 bg-white/60 dark:bg-slate-700/50 rounded-lg px-4 py-2 text-slate-900 dark:text-white focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none" placeholder="Phone number">
        </div>
        <div>
            <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Address (optional)</label>
            <textarea name="address" rows="2" class="w-full border border-slate-300 dark:border-white/20 bg-white/60 dark:bg-slate-700/50 rounded-lg px-4 py-2 text-slate-900 dark:text-white focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none" placeholder="Address"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Create Buyer
        </button>
    </form>
</div>
@endsection