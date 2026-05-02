@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('landings.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Landings</a>
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-off-black dark:text-white">Landing Settlement</h1>
            <p class="text-black-50 dark:text-slate-400">{{ $landing->boat->name }} - {{ $landing->date->format('d M Y') }}</p>
        </div>
        <span class="px-4 py-2 rounded-lg text-sm font-medium 
            @if($landing->status === 'Settled') bg-green-500/20 text-green-400
            @elseif($landing->status === 'Partial') bg-yellow-500/20 text-yellow-400
            @else bg-gray-500/20 text-black-50 @endif">
            {{ $landing->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="card rounded-xl p-4">
        <p class="text-black-50 dark:text-slate-400 text-xs">Gross Sale</p>
        <p class="text-xl font-bold text-off-black dark:text-white">₹{{ number_format($summary['gross_value'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 dark:text-slate-400 text-xs">Total Expenses</p>
        <p class="text-xl font-bold text-orange-400">₹{{ number_format($summary['total_expenses'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 dark:text-slate-400 text-xs">Net Owner Payable</p>
        <p class="text-xl font-bold text-blue-400">₹{{ number_format($summary['net_owner_payable'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 dark:text-slate-400 text-xs">Owner Paid</p>
        <p class="text-xl font-bold text-green-400">₹{{ number_format($summary['total_owner_paid'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4">
        <p class="text-black-50 dark:text-slate-400 text-xs">Owner Pending</p>
        <p class="text-xl font-bold text-red-400">₹{{ number_format($summary['owner_pending'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="card rounded-xl p-4 border-l-4 border-green-500">
        <p class="text-black-50 text-xs">Cash Receipts</p>
        <p class="text-lg font-bold text-green-400">₹{{ number_format($receiptSummary['cash'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-blue-500">
        <p class="text-black-50 text-xs">Bank/GP Receipts</p>
        <p class="text-lg font-bold text-blue-400">₹{{ number_format($receiptSummary['bank'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-green-500">
        <p class="text-black-50 text-xs">Cash Payments</p>
        <p class="text-lg font-bold text-green-400">₹{{ number_format($paymentSummary['cash'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-blue-500">
        <p class="text-black-50 text-xs">Bank/GP Payments</p>
        <p class="text-lg font-bold text-blue-400">₹{{ number_format($paymentSummary['bank'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black dark:text-white mb-4">Invoices</h2>
        @if($landing->invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-black-50 border-b border-gray-700">
                    <tr>
                        <th class="text-left py-2">Buyer</th>
                        <th class="text-right py-2">Amount</th>
                        <th class="text-right py-2">Received</th>
                        <th class="text-right py-2">Pending</th>
                        <th class="text-center py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="text-off-black dark:text-white">
                    @foreach($landing->invoices as $invoice)
                    <tr class="border-b border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="py-2">{{ $invoice->buyer->name }}</td>
                        <td class="text-right py-2">₹{{ number_format($invoice->original_amount, 2) }}</td>
                        <td class="text-right py-2 text-green-400">₹{{ number_format($invoice->received_amount, 2) }}</td>
                        <td class="text-right py-2 text-red-400">₹{{ number_format($invoice->pending_amount, 2) }}</td>
                        <td class="text-center py-2">
                            <span class="px-2 py-1 rounded text-xs 
                                @if($invoice->status === 'Paid') bg-green-500/20 text-green-400
                                @elseif($invoice->status === 'Partial') bg-yellow-500/20 text-yellow-400
                                @else bg-gray-500/20 text-black-50 @endif">
                                {{ $invoice->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 dark:text-slate-400">No invoices yet</p>
        @endif
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black dark:text-white mb-4">Expenses</h2>
        @if($landing->expenses->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-black-50 border-b border-gray-700">
                    <tr>
                        <th class="text-left py-2">Type</th>
                        <th class="text-left py-2">Vendor</th>
                        <th class="text-right py-2">Amount</th>
                        <th class="text-right py-2">Paid</th>
                        <th class="text-right py-2">Pending</th>
                        <th class="text-center py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="text-off-black dark:text-white">
                    @foreach($landing->expenses as $expense)
                    <tr class="border-b border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="py-2">{{ $expense->type }}</td>
                        <td class="py-2">{{ $expense->vendor_name ?? '-' }}</td>
                        <td class="text-right py-2">₹{{ number_format($expense->amount, 2) }}</td>
                        <td class="text-right py-2 text-green-400">₹{{ number_format($expense->paid_amount, 2) }}</td>
                        <td class="text-right py-2 text-red-400">₹{{ number_format($expense->pending_amount, 2) }}</td>
                        <td class="text-center py-2">
                            <span class="px-2 py-1 rounded text-xs 
                                @if($expense->status === 'Paid') bg-green-500/20 text-green-400
                                @elseif($expense->status === 'Partial') bg-yellow-500/20 text-yellow-400
                                @else bg-gray-500/20 text-black-50 @endif">
                                {{ $expense->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 dark:text-slate-400">No expenses yet</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black dark:text-white mb-4">Receipts (Buyer Payments)</h2>
        @if($landing->receipts->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-black-50 border-b border-gray-700">
                    <tr>
                        <th class="text-left py-2">Buyer</th>
                        <th class="text-left py-2">Date</th>
                        <th class="text-right py-2">Amount</th>
                        <th class="text-center py-2">Mode</th>
                    </tr>
                </thead>
                <tbody class="text-off-black dark:text-white">
                    @foreach($landing->receipts as $receipt)
                    <tr class="border-b border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="py-2">{{ $receipt->buyer->name }}</td>
                        <td class="py-2">{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</td>
                        <td class="text-right py-2">₹{{ number_format($receipt->amount, 2) }}</td>
                        <td class="text-center py-2">
                            <span class="px-2 py-1 rounded text-xs 
                                @if($receipt->mode === 'Cash') bg-green-500/20 text-green-400
                                @else bg-blue-500/20 text-blue-400 @endif">
                                {{ $receipt->mode }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500">No receipts yet</p>
        @endif
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-off-black dark:text-white mb-4">Payments to Owner</h2>
        @if($ownerPayments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-black-50 border-b border-gray-700">
                    <tr>
                        <th class="text-left py-2">Date</th>
                        <th class="text-right py-2">Amount</th>
                        <th class="text-center py-2">Mode</th>
                        <th class="text-left py-2">Source</th>
                    </tr>
                </thead>
                <tbody class="text-off-black dark:text-white">
                    @foreach($ownerPayments as $payment)
                    <tr class="border-b border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="py-2">{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                        <td class="text-right py-2">₹{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-center py-2">
                            <span class="px-2 py-1 rounded text-xs 
                                @if($payment->mode === 'Cash') bg-green-500/20 text-green-400
                                @else bg-blue-500/20 text-blue-400 @endif">
                                {{ $payment->mode }}
                            </span>
                        </td>
                        <td class="py-2">{{ $payment->source }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 dark:text-slate-400">No payments yet</p>
        @endif
    </div>
</div>

@if($landing->notes)
<div class="card rounded-xl p-6 mt-6">
        <h2 class="text-xl font-bold text-off-black dark:text-white mb-2">Notes</h2>
        <p class="text-black-50 dark:text-slate-400">{{ $landing->notes }}</p>
</div>
@endif

<div id="editInvoiceModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Edit Invoice</h3>
            <button onclick="closeModal('editInvoiceModal')" class="text-black-50 hover:text-off-black">
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
                    <label class="block text-sm text-black-50 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" id="editInvoiceDate" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Original Amount</label>
                    <input type="number" name="original_amount" id="editInvoiceAmount" required step="0.01" min="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Notes</label>
                    <textarea name="notes" id="editInvoiceNotes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editInvoiceModal')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update</button>
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
                    <label class="block text-sm text-black-50 mb-1">Boat</label>
                    <select name="boat_id" id="editExpenseBoat" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
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
                            @foreach($expenseTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
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

function openEditInvoiceModal(invoice) {
    document.getElementById('editInvoiceForm').action = '/invoices/' + invoice.id;
    document.getElementById('editInvoiceDate').value = invoice.invoice_date;
    document.getElementById('editInvoiceAmount').value = invoice.original_amount;
    document.getElementById('editInvoiceNotes').value = invoice.notes || '';
    document.getElementById('editInvoiceModal').classList.remove('hidden');
}

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
</script>
@endsection
