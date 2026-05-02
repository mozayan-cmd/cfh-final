@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Invoices</h1>
        <p class="text-slate-500">Manage buyer invoices</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('invoices.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            Export CSV
        </a>
        <a href="{{ route('invoices.import') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            Import
        </a>
        <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + New Invoice
        </a>
    </div>
</div>

<div class="card rounded-xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Buyer</label>
                <select name="buyer_id" id="filterBuyer" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Buyers</option>
                    <option value="pending_only" {{ request('buyer_id') == 'pending_only' ? 'selected' : '' }}>--- Buyers with Pending Invoices ---</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>{{ $buyer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Boat</label>
                <select name="boat_id" id="filterBoat" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Boats</option>
                    @foreach($boats as $boat)
                        <option value="{{ $boat->id }}" {{ request('boat_id') == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Filter by Landing Date</label>
                <select name="landing_id" id="filterLanding" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none" {{ !request('boat_id') ? 'disabled' : '' }}>
                    <option value="">All Landing Dates</option>
                    @if(request('boat_id') && request('landing_id'))
                        @foreach($landings->where('boat_id', request('boat_id')) as $landing)
                            <option value="{{ $landing->id }}" {{ request('landing_id') == $landing->id ? 'selected' : '' }}>{{ $landing->date->format('Y-m-d') }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Status</label>
                <select name="status" id="filterStatus" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-fin-orange dark:focus:border-fin-orange/50 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Partial" {{ request('status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <div class="bg-cyan-50 dark:bg-cyan-900/30 rounded-lg p-3 text-center border border-cyan-200 dark:border-cyan-700/50">
                <p class="text-xs sm:text-sm text-cyan-700 dark:text-cyan-300">Total Value</p>
                <p class="text-lg sm:text-xl font-bold text-cyan-800 dark:text-cyan-200">₹{{ number_format($totalOriginal, 2) }}</p>
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
        
        <div class="flex gap-3 mt-4">
            <a href="{{ route('invoices.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg">Clear</a>
        </div>
    </div>
</form>

<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 280px);">
    <div class="overflow-x-auto flex-1 min-h-0">
    <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-slate-600 dark:text-slate-300 text-sm font-medium sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                <th class="text-left px-6 py-4">Date</th>
                <th class="text-left px-6 py-4">Buyer</th>
                <th class="text-left px-6 py-4">Boat</th>
                <th class="text-left px-6 py-4">Landing</th>
                <th class="text-right px-6 py-4">Original</th>
                <th class="text-right px-6 py-4">Received</th>
                <th class="text-right px-6 py-4">Pending</th>
                <th class="text-center px-6 py-4">Status</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $invoice->buyer->name }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $invoice->boat->name }}</td>
                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">{{ $invoice->landing->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-right text-slate-900 dark:text-slate-200">₹{{ number_format($invoice->original_amount, 2) }}</td>
                <td class="px-6 py-4 text-right text-green-600 dark:text-green-400">₹{{ number_format($invoice->received_amount, 2) }}</td>
                <td class="px-6 py-4 text-right text-yellow-600 dark:text-yellow-400">₹{{ number_format($invoice->pending_amount, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($invoice->status === 'Paid') bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300
                        @elseif($invoice->status === 'Partial') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300
                        @else bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 @endif">
                        {{ $invoice->status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick='openEditInvoiceModal({{ json_encode(["id" => $invoice->id, "invoice_date" => $invoice->invoice_date->format("Y-m-d"), "original_amount" => $invoice->original_amount, "notes" => $invoice->notes]) }})' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2">Edit</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-300 mr-2">View</a>
                    <button onclick="openDeleteModal('{{ route('invoices.destroy', $invoice) }}', 'invoice', {{ $invoice->id }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-slate-500">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="editInvoiceModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-900">Edit Invoice</h3>
            <button onclick="closeModal('editInvoiceModal')" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editInvoiceForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" id="editInvoiceDate" required class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Original Amount</label>
                    <input type="number" name="original_amount" id="editInvoiceAmount" required step="0.01" min="0.01" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Notes</label>
                    <textarea name="notes" id="editInvoiceNotes" rows="2" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editInvoiceModal')" class="px-4 py-2 text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="bg-fin-orange hover:bg-fin-orange/90 text-white px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openEditInvoiceModal(invoice) {
    document.getElementById('editInvoiceForm').action = '/invoices/' + invoice.id;
    document.getElementById('editInvoiceDate').value = invoice.invoice_date;
    document.getElementById('editInvoiceAmount').value = invoice.original_amount;
    document.getElementById('editInvoiceNotes').value = invoice.notes || '';
    document.getElementById('editInvoiceModal').classList.remove('hidden');
}

// Real-time filtering: auto-submit when boat changes
document.getElementById('filterBoat').addEventListener('change', function() {
    const boatId = this.value;
    const landingSelect = document.getElementById('filterLanding');
    
    // Clear landing dropdown
    landingSelect.innerHTML = '<option value="">Loading...</option>';
    landingSelect.disabled = !boatId;
    
    if (boatId) {
        // Fetch landing dates with pending invoices for selected boat
        fetch(`/invoices/pending-landings/${boatId}`)
            .then(response => response.json())
            .then(data => {
                landingSelect.innerHTML = '<option value="">All Landing Dates</option>';
                data.forEach(landing => {
                    landingSelect.innerHTML += `<option value="${landing.id}" data-pending="${landing.pending_amount}">${landing.date} - ₹${Number(landing.pending_amount).toLocaleString('en-IN')} pending</option>`;
                });
            })
            .catch(() => {
                landingSelect.innerHTML = '<option value="">Error loading</option>';
            });
    } else {
        landingSelect.innerHTML = '<option value="">All Landing Dates</option>';
    }
});

// Real-time filtering: auto-submit when landing changes
document.getElementById('filterLanding').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Auto-submit on buyer change
document.getElementById('filterBuyer').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Auto-submit on status change
document.getElementById('filterStatus').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endsection
