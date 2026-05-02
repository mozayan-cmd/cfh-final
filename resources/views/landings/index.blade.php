@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-off-black dark:text-white">Landings</h1>
        <p class="text-slate-500 dark:text-slate-400">Manage boat landings</p>
    </div>
    <button onclick="openModal('createLandingModal')" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
        + New Landing
    </button>
</div>

<div class="card rounded-xl overflow-hidden">
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium">
                <th class="text-left px-6 py-4">Date</th>
                <th class="text-left px-6 py-4">Boat</th>
                <th class="text-right px-6 py-4">Gross Value</th>
                <th class="text-right px-6 py-4">Expenses</th>
                <th class="text-right px-6 py-4">Pending</th>
                <th class="text-right px-6 py-4">Net Payable</th>
                <th class="text-right px-6 py-4">Owner Paid</th>
                <th class="text-right px-6 py-4">Pending</th>
                <th class="text-center px-6 py-4">Status</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($landings as $landing)
            <tr class="border-t border-slate-200 hover:bg-slate-50">
                <td class="px-6 py-4 text-off-black dark:text-white">{{ $landing->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-off-black dark:text-white">{{ $landing->boat->name }}</td>
                <td class="px-6 py-4 text-right text-off-black dark:text-white">₹{{ number_format($landing->gross_value, 2) }}</td>
                <td class="px-6 py-4 text-right text-orange-600">₹{{ number_format($landing->summary['total_expenses'], 2) }}</td>
                <td class="px-6 py-4 text-right text-orange-500">₹{{ number_format($landing->summary['total_expenses_pending'], 2) }}</td>
                <td class="px-6 py-4 text-right text-off-black dark:text-white">₹{{ number_format($landing->summary['net_owner_payable'], 2) }}</td>
                <td class="px-6 py-4 text-right text-green-600">₹{{ number_format($landing->summary['total_owner_paid'], 2) }}</td>
                <td class="px-6 py-4 text-right text-yellow-600">₹{{ number_format($landing->summary['owner_pending'], 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($landing->status === 'Settled') bg-green-100 text-green-700
                        @elseif($landing->status === 'Partial') bg-yellow-100 text-yellow-700
                        @else bg-slate-100 text-slate-600 @endif">
                        {{ $landing->status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick='openEditLandingModal({{ json_encode(["id" => $landing->id, "boat_id" => $landing->boat_id, "date" => $landing->date->format("Y-m-d"), "gross_value" => $landing->gross_value, "notes" => $landing->notes]) }})' class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                    <a href="{{ route('landings.show', $landing) }}" class="text-slate-600 hover:text-slate-800 mr-2">View</a>
                    <button onclick="openDeleteModal('{{ route('landings.destroy', $landing) }}', 'landing', {{ $landing->id }})" class="text-red-600 hover:text-red-800">Delete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-8 text-center text-slate-500">No landings found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="createLandingModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">New Landing</h3>
            <button onclick="closeModal('createLandingModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('landings.store') }}" method="POST" id="landingForm">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat</label>
                    <select name="boat_id" id="landingBoatId" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" id="landingDate" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Gross Sale Value</label>
                    <input type="number" name="gross_value" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div id="selectedExpenseIdsContainer"></div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('createLandingModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="button" onclick="showUnlinkedExpenses()" class="bg-orange-600 hover:bg-orange-700 text-off-black px-4 py-2 rounded-lg">Link Expenses</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Create Landing</button>
            </div>
        </form>
    </div>
</div>

<div id="unlinkedExpensesModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Link Unlinked Expenses</h3>
            <button onclick="closeModal('unlinkedExpensesModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-black-50 mb-4">Select expenses to link with this landing:</p>
        <div id="unlinkedExpensesList" class="max-h-60 overflow-y-auto space-y-2 mb-4">
        </div>
        <div class="flex justify-between items-center mb-4">
            <label class="flex items-center text-black-50">
                <input type="checkbox" id="selectAllExpenses" onclick="toggleAllExpenses()" class="mr-2">
                Select All
            </label>
            <span id="selectedCount" class="text-black-50">0 selected</span>
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeModal('unlinkedExpensesModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
            <button type="button" onclick="confirmExpenses()" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Confirm & Create Landing</button>
        </div>
    </div>
</div>

<div id="editLandingModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Edit Landing</h3>
            <button onclick="closeModal('editLandingModal')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editLandingForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" id="editLandingDate" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Gross Sale Value</label>
                    <input type="number" name="gross_value" id="editLandingValue" required step="0.01" min="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" id="editLandingNotes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editLandingModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openEditLandingModal(landing) {
    document.getElementById('editLandingForm').action = '/landings/' + landing.id;
    document.getElementById('editLandingDate').value = landing.date;
    document.getElementById('editLandingValue').value = landing.gross_value;
    document.getElementById('editLandingNotes').value = landing.notes || '';
    document.getElementById('editLandingModal').classList.remove('hidden');
}

const unlinkedExpensesData = @json($unlinkedExpenses);

function showUnlinkedExpenses() {
    const boatId = document.getElementById('landingBoatId').value;
    const date = document.getElementById('landingDate').value;
    
    if (!boatId) {
        alert('Please select a boat first');
        return;
    }
    
    const expenses = unlinkedExpensesData[boatId] || [];
    const listEl = document.getElementById('unlinkedExpensesList');
    
    if (expenses.length === 0) {
        listEl.innerHTML = '<p class="text-gray-500 italic">No unlinked expenses for this boat.</p>';
    } else {
        listEl.innerHTML = expenses.map(e => `
            <label class="flex items-center p-3 rounded-lg bg-slate-50/40 dark:bg-slate-700/30 hover:bg-gray-700/50 cursor-pointer">
                <input type="checkbox" name="expense_item" value="${e.id}" class="mr-3 expense-checkbox" data-amount="${e.amount}">
                <div class="flex-1">
                    <span class="text-off-black">${e.type}</span>
                    <span class="text-black-50 ml-2">${e.date}</span>
                </div>
                <span class="text-off-black">₹${parseFloat(e.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
            </label>
        `).join('');
        
        document.getElementById('selectAllExpenses').checked = false;
        updateSelectedCount();
        
        document.querySelectorAll('.expense-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
    }
    
    openModal('unlinkedExpensesModal');
}

function toggleAllExpenses() {
    const selectAll = document.getElementById('selectAllExpenses');
    document.querySelectorAll('.expense-checkbox').forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.expense-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected + ' selected';
}

function confirmExpenses() {
    const selectedIds = Array.from(document.querySelectorAll('.expense-checkbox:checked')).map(cb => cb.value);
    const container = document.getElementById('selectedExpenseIdsContainer');
    container.innerHTML = selectedIds.map(id => `<input type="hidden" name="expense_ids[]" value="${id}">`).join('');
    
    closeModal('unlinkedExpensesModal');
    
    document.getElementById('landingForm').submit();
}
</script>
@endsection
