@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Expenses</h1>
        <p class="text-slate-500">Track all expenses</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('expenses.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            Export CSV
        </a>
        <a href="{{ route('expenses.import') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            Import
        </a>
        <button onclick="openModal('addTypeModal')" 
            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg">
            + Add Type
        </button>
        <button onclick="openModal('createExpenseModal')" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + New Expense
        </button>
    </div>
</div>

<form id="filterForm" method="GET" action="{{ route('expenses.index') }}" class="mb-6">
    <div class="card rounded-xl p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Boat</label>
                <select name="boat_id" id="filterBoat" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Boats</option>
                    @foreach($boats as $boat)
                        <option value="{{ $boat->id }}" {{ request('boat_id') == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Type</label>
                <select name="type" id="filterType" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->name }}" {{ request('type') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Vendor/Person</label>
                <select name="vendor_status" id="filterVendor" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Vendors</option>
                    <option value="" disabled>-------------------------</option>
                    <option value="pending" {{ request('vendor_status') == 'pending' ? 'selected' : '' }}>Vendors with Pending Payments</option>
                    <option value="paid" {{ request('vendor_status') == 'paid' ? 'selected' : '' }}>Fully Paid Vendors</option>
                </select>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('expenses.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg h-[42px] flex items-center">Clear</a>
            </div>
            <div class="flex gap-3">
                <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-3 text-center border border-yellow-200 dark:border-yellow-700/50">
                    <p class="text-xs text-yellow-700 dark:text-yellow-300">Total Amount</p>
                    <p class="text-lg font-bold text-yellow-800 dark:text-yellow-200">₹{{ number_format($totalAmount ?? 0, 2) }}</p>
                </div>
                <div class="bg-cyan-50 dark:bg-cyan-900/30 rounded-lg p-3 text-center border border-cyan-200 dark:border-cyan-700/50">
                    <p class="text-xs text-cyan-700 dark:text-cyan-300">Pending Amount</p>
                    <p class="text-lg font-bold text-cyan-800 dark:text-cyan-200">₹{{ number_format($totalPending ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 260px);">
    <div class="overflow-x-auto flex-1 min-h-0">
    <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                <th class="text-left px-6 py-4">Date</th>
                <th class="text-left px-6 py-4">Boat</th>
                <th class="text-left px-6 py-4">Type</th>
                <th class="text-left px-6 py-4">Vendor</th>
                <th class="text-right px-6 py-4">Amount</th>
                <th class="text-right px-6 py-4">Paid</th>
                <th class="text-right px-6 py-4">Pending</th>
                <th class="text-center px-6 py-4">Status</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/30 
                {{ $expense->payment_status === 'Paid' ? 'bg-green-100/60 dark:bg-green-900/20' : '' }}">
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $expense->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $expense->boat->name ?? '-' }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $expense->type }}</td>
                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                    @php
                        $allocation = $expense->paymentAllocations()->with('payment')->first();
                        $payment = $allocation ? $allocation->payment : null;
                    @endphp
                    @if($payment && $payment->payment_for === 'Other' && $payment->vendor_name)
                        {{ $payment->vendor_name }}
                    @elseif($expense->vendor_name)
                        {{ $expense->vendor_name }}
                    @else
                        -
                    @endif
                </td>
                <td class="px-6 py-4 text-right text-slate-900 dark:text-slate-200">₹{{ number_format($expense->amount, 2) }}</td>
                <td class="px-6 py-4 text-right text-green-600 dark:text-green-400">₹{{ number_format($expense->paid_amount, 2) }}</td>
                <td class="px-6 py-4 text-right text-yellow-600 dark:text-yellow-400">₹{{ number_format($expense->pending_amount, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($expense->payment_status === 'Paid') bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300
                        @elseif($expense->payment_status === 'Partial') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300
                        @else bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 @endif">
                        {{ $expense->payment_status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick='openEditExpenseModal({{ json_encode(["id" => $expense->id, "boat_id" => $expense->boat_id, "landing_id" => $expense->landing_id, "date" => $expense->date->format("Y-m-d"), "type" => $expense->type, "vendor_name" => $expense->vendor_name, "amount" => $expense->amount, "notes" => $expense->notes]) }})' class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-2">Edit</button>
                    <a href="{{ route('payments.create', ['expense_id' => $expense->id]) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 mr-2">Pay</a>
                    <button onclick="openDeleteModal('{{ route('expenses.destroy', $expense) }}', 'expense', {{ $expense->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Delete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">No expenses found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="createExpenseModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">New Expense</h3>
            <button onclick="closeModal('createExpenseModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat (Optional)</label>
                    <select name="boat_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">No Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing (Optional)</label>
                    <select name="landing_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">No Landing</option>
                        <option value="next">Next landing (auto‑assign)</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}">{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Date</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Type</label>
                        <select name="type" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                            @foreach($types as $type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Vendor/Person</label>
                    <input type="text" name="vendor_name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount</label>
                    <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('createExpenseModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="addTypeModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Add Expense Type</h3>
            <button onclick="closeModal('addTypeModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('expenses.store-type') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Type Name</label>
                    <input type="text" name="name" required placeholder="e.g., Diesel, Ice, Ration" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('addTypeModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-off-black px-4 py-2 rounded-lg">Add Type</button>
            </div>
        </form>
    </div>
</div>

<div id="editExpenseModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Edit Expense</h3>
            <button onclick="closeModal('editExpenseModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editExpenseForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat (Optional)</label>
                    <select name="boat_id" id="editExpenseBoat" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">No Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Date</label>
                        <input type="date" name="date" id="editExpenseDate" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Type</label>
                        <select name="type" id="editExpenseType" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                            @foreach($types as $type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Vendor/Person</label>
                    <input type="text" name="vendor_name" id="editExpenseVendor" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount</label>
                    <input type="number" name="amount" id="editExpenseAmount" required step="0.01" min="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" id="editExpenseNotes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editExpenseModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openEditExpenseModal(expense) {
    document.getElementById('editExpenseForm').action = '/expenses/' + expense.id;
    document.getElementById('editExpenseBoat').value = expense.boat_id;
    document.getElementById('editExpenseDate').value = expense.date;
    document.getElementById('editExpenseType').value = expense.type;
    document.getElementById('editExpenseVendor').value = expense.vendor_name || '';
    document.getElementById('editExpenseAmount').value = expense.amount;
    document.getElementById('editExpenseNotes').value = expense.notes || '';
    document.getElementById('editExpenseModal').classList.remove('hidden');
}

// Auto-submit on filter changes
document.getElementById('filterBoat').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterType').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('filterVendor').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

function openDeleteModal(formAction, modelType, recordId) {
    fetch(`/api/related-records/${modelType}/${recordId}`)
        .then(res => res.json())
        .then(data => {
            const modal = document.getElementById('deleteConfirmModal');
            const relatedList = document.getElementById('relatedList');
            const warningBox = document.getElementById('deleteWarning');
            
            document.getElementById('deleteForm').action = formAction;
            
            if (data.length > 0) {
                warningBox.classList.remove('hidden');
                relatedList.innerHTML = data.map(item => 
                    `<div class="flex justify-between text-sm">
                        <span class="text-off-black">${item.type}</span>
                        <span class="text-off-black">${item.count} (₹${Number(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})})</span>
                    </div>`
                ).join('');
            } else {
                warningBox.classList.add('hidden');
                relatedList.innerHTML = '';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Delete Expense</h3>
            <button onclick="closeDeleteModal()" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="deleteWarning" class="hidden bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4 mb-4">
            <p class="text-yellow-400 font-medium mb-2">Warning: This expense has related records!</p>
            <p class="text-black-50 text-sm mb-3">Deleting this expense will leave the following records orphaned:</p>
            <div id="relatedList" class="space-y-2"></div>
            <p class="text-yellow-400 text-sm mt-3 font-medium">Are you sure you want to delete this expense?</p>
        </div>
        
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
            <p class="text-red-400 font-medium">Are you sure you want to delete this expense?</p>
            <p class="text-black-50 text-sm mt-1">This action cannot be undone.</p>
        </div>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection
