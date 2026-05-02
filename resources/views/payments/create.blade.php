@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('payments.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Payments</a>
    <h1 class="text-3xl font-bold text-off-black">New Payment</h1>
</div>

<div class="card rounded-xl p-6 max-w-3xl">
    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat (Optional)</label>
                    <select name="boat_id" id="boat_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Boat (Optional)</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}" {{ $preselectedBoatId == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing (for Owner Payments)</label>
                    <select name="landing_id" id="landing_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Landing (Optional)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div id="expenseSection">
                <label class="block text-sm text-black-50 mb-1">Expense to Pay (Optional)</label>
                <select name="expense_id" id="expense_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                    <option value="">Select an expense (optional)</option>
                </select>
            </div>

            <div id="expenseInfo" class="hidden card p-4 rounded-lg bg-green-900/30 border border-green-700/50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-black-50 text-sm">Expense</p>
                        <p class="text-off-black font-semibold" id="expenseType">-</p>
                        <p class="text-black-50 text-sm" id="expenseVendor">-</p>
                    </div>
                    <div class="text-right">
                        <p class="text-black-50 text-sm">Outstanding Amount</p>
                        <p class="text-yellow-400 text-2xl font-bold" id="outstandingAmount">₹0.00</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Payment Amount</label>
                    <input type="number" name="amount" id="amount" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Remaining After Payment</label>
                    <div class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2">
                        <span class="text-lg font-bold" id="remainingAmount">₹0.00</span>
                        <span class="text-sm text-black-50 ml-2" id="paymentStatus"></span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Mode</label>
                    <select name="mode" id="payment_mode" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                        @foreach($modes as $mode)
                            <option value="{{ $mode }}">{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Payment For</label>
                    <select name="payment_for" id="payment_for" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                        @foreach($paymentFors as $for)
                            <option value="{{ $for }}">{{ $for }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="cashSourceSection" class="hidden">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Paid from Cash Receipt (Optional)</label>
                    <select name="cash_source_receipt_id" id="cash_source_receipt_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                        <option value="">Select cash receipt (optional)</option>
                        @foreach($availableCashReceipts as $receipt)
                            <option value="{{ $receipt->id }}" data-balance="{{ $receipt->balance }}">
                                {{ $receipt->buyer->name ?? 'N/A' }} - ₹{{ number_format($receipt->amount, 2) }} (Available: ₹{{ number_format($receipt->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-gray-500 text-xs mt-1">Link this payment to a specific cash receipt</p>
                </div>
            </div>

            <div id="loanReferenceSection" class="hidden">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Loan Reference / Description</label>
                    <input type="text" name="loan_reference" id="loan_reference" placeholder="e.g., SBI Loan EMI, Bank Interest" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div id="vendorNameSection" class="hidden">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Vendor Name <span class="text-red-400">*</span></label>
                    <input type="text" name="vendor_name" id="vendor_name" placeholder="Enter the party name to whom payment is made" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white focus:border-blue-500 focus:outline-none"></textarea>
            </div>

            <div id="allocationFields"></div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('payments.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Record Payment</button>
        </div>
    </form>
</div>

<script>
let pendingExpenses = [];
let selectedExpense = null;
const preselectedBoatId = @json($preselectedBoatId);
const preselectedExpense = @json($preselectedExpense);
const bankBalance = @json($bankBalance);
const totalAvailableCash = @json($totalAvailableCash);

function initializeForm() {
    toggleLoanReference();
    toggleVendorName();
    toggleCashSource();
    
    if (preselectedBoatId) {
        document.getElementById('boat_id').value = preselectedBoatId;
        loadLandings(preselectedBoatId);
        loadExpenses(preselectedBoatId);
    }
    
    if (preselectedExpense) {
        loadExpenses(preselectedExpense.boat_id || preselectedBoatId, preselectedExpense);
    }
}

function loadLandings(boatId) {
    const landingSelect = document.getElementById('landing_id');
    
    if (!boatId) {
        landingSelect.innerHTML = '<option value="">Select Landing (Optional)</option>';
        return;
    }
    
    landingSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`/payments/landings/${boatId}`).then(r => r.json()).then(data => {
        landingSelect.innerHTML = '<option value="">Select Landing (Optional)</option>';
        
        if (data.landings && data.landings.length > 0) {
            data.landings.forEach(landing => {
                landingSelect.innerHTML += `<option value="${landing.id}">${landing.date} - Owner Payable: ₹${landing.net_owner_payable.toLocaleString('en-IN')} (Pending: ₹${landing.owner_pending.toLocaleString('en-IN')})</option>`;
            });
        }
    });
}

function toggleLoanReference() {
    const paymentFor = document.getElementById('payment_for').value;
    const loanSection = document.getElementById('loanReferenceSection');
    const loanInput = document.getElementById('loan_reference');
    
    if (paymentFor === 'Loan') {
        loanSection.classList.remove('hidden');
        loanInput.required = true;
    } else {
        loanSection.classList.add('hidden');
        loanInput.required = false;
        loanInput.value = '';
    }
}

function toggleVendorName() {
    const paymentFor = document.getElementById('payment_for').value;
    const vendorSection = document.getElementById('vendorNameSection');
    const vendorInput = document.getElementById('vendor_name');
    
    if (paymentFor === 'Other') {
        vendorSection.classList.remove('hidden');
        vendorInput.required = true;
    } else {
        vendorSection.classList.add('hidden');
        vendorInput.required = false;
        vendorInput.value = '';
    }
}

function toggleCashSource() {
    const mode = document.getElementById('payment_mode').value;
    const cashSourceSection = document.getElementById('cashSourceSection');
    
    if (mode === 'Cash') {
        cashSourceSection.classList.remove('hidden');
    } else {
        cashSourceSection.classList.add('hidden');
        document.getElementById('cash_source_receipt_id').value = '';
    }
}

function validateAmount() {
    const amountInput = document.getElementById('amount');
    const amount = parseFloat(amountInput.value) || 0;
    const mode = document.getElementById('payment_mode').value;
    
    let maxAllowed = Infinity;
    let balanceType = '';
    
    if (mode === 'Cash') {
        const receiptSelect = document.getElementById('cash_source_receipt_id');
        const selectedOption = receiptSelect.options[receiptSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
            maxAllowed = balance;
            balanceType = 'cash receipt';
        } else {
            maxAllowed = totalAvailableCash;
            balanceType = 'available cash';
        }
    } else if (['Bank', 'GP'].includes(mode)) {
        maxAllowed = bankBalance;
        balanceType = 'bank';
    }
    
    if (amount > maxAllowed) {
        alert(`Payment amount exceeds available ${balanceType} balance. Maximum allowed: ₹${maxAllowed.toFixed(2)}`);
        amountInput.value = maxAllowed.toFixed(2);
    }
}

document.getElementById('payment_for').addEventListener('change', toggleLoanReference);
document.getElementById('payment_for').addEventListener('change', toggleVendorName);
document.getElementById('payment_for').addEventListener('change', validateAmount);
document.getElementById('payment_mode').addEventListener('change', toggleCashSource);
document.getElementById('payment_mode').addEventListener('change', validateAmount);
document.getElementById('amount').addEventListener('input', validateAmount);
document.getElementById('cash_source_receipt_id').addEventListener('change', validateAmount);

document.getElementById('boat_id').addEventListener('change', function() {
    loadLandings(this.value);
});

function loadExpenses(boatId, preSelect = null) {
    const expenseSelect = document.getElementById('expense_id');
    
    if (!boatId) {
        expenseSelect.innerHTML = '<option value="">Select an expense (optional)</option>';
        return;
    }
    
    expenseSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`/payments/expenses/${boatId}`).then(r => r.json()).then(data => {
        pendingExpenses = data.expenses;
        
        if (pendingExpenses.length === 0) {
            expenseSelect.innerHTML = '<option value="">No pending expenses for this boat</option>';
            return;
        }
        
        expenseSelect.innerHTML = '<option value="">Select an expense (optional)</option>';
        pendingExpenses.forEach(exp => {
            const selected = preSelect && preSelect.id === exp.id ? 'selected' : '';
            expenseSelect.innerHTML += `<option value="${exp.id}" data-type="${exp.type}" data-vendor="${exp.vendor_name || ''}" data-pending="${exp.pending_amount}" ${selected}>${exp.type}${exp.vendor_name ? ' - ' + exp.vendor_name : ''} (₹${exp.pending_amount} pending)</option>`;
        });
        
        if (preSelect) {
            expenseSelect.dispatchEvent(new Event('change'));
        }
    });
}

document.getElementById('boat_id').addEventListener('change', function() {
    loadExpenses(this.value);
    clearExpenseInfo();
});

document.getElementById('expense_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    
    if (!this.value) {
        clearExpenseInfo();
        return;
    }
    
    selectedExpense = {
        id: parseInt(this.value),
        type: option.dataset.type,
        vendor: option.dataset.vendor,
        pending: parseFloat(option.dataset.pending)
    };
    
    document.getElementById('expenseInfo').classList.remove('hidden');
    document.getElementById('expenseType').textContent = selectedExpense.type;
    document.getElementById('expenseVendor').textContent = selectedExpense.vendor || '-';
    document.getElementById('outstandingAmount').textContent = '₹' + selectedExpense.pending.toLocaleString('en-IN');
    
    document.getElementById('payment_for').value = 'Expense';
    
    if (!document.getElementById('amount').value) {
        document.getElementById('amount').value = selectedExpense.pending;
        updateRemaining();
    }
});

function clearExpenseInfo() {
    selectedExpense = null;
    document.getElementById('expenseInfo').classList.add('hidden');
    document.getElementById('expenseType').textContent = '-';
    document.getElementById('expenseVendor').textContent = '-';
    document.getElementById('outstandingAmount').textContent = '₹0.00';
    updateRemaining();
}

document.getElementById('amount').addEventListener('input', updateRemaining);

function updateRemaining() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const remainingSpan = document.getElementById('remainingAmount');
    const statusSpan = document.getElementById('paymentStatus');
    
    if (!selectedExpense) {
        remainingSpan.textContent = '₹0.00';
        remainingSpan.className = 'text-lg font-bold';
        statusSpan.textContent = '';
        return;
    }
    
    const remaining = selectedExpense.pending - amount;
    remainingSpan.textContent = '₹' + Math.abs(remaining).toLocaleString('en-IN');
    
    if (remaining === 0) {
        remainingSpan.className = 'text-lg font-bold text-green-400';
        statusSpan.textContent = '(Full Payment)';
    } else if (remaining > 0) {
        remainingSpan.className = 'text-lg font-bold text-yellow-400';
        statusSpan.textContent = '(Partial Payment)';
    } else {
        remainingSpan.className = 'text-lg font-bold text-red-400';
        statusSpan.textContent = '(Exceeds Outstanding)';
    }
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const allocationFields = document.getElementById('allocationFields');
    allocationFields.innerHTML = '';
    
    if (selectedExpense && document.getElementById('amount').value) {
        const amount = parseFloat(document.getElementById('amount').value);
        allocationFields.innerHTML = `
            <input type="hidden" name="allocations[0][type]" value="expense">
            <input type="hidden" name="allocations[0][id]" value="${selectedExpense.id}">
            <input type="hidden" name="allocations[0][amount]" value="${amount}">
        `;
    }
});

document.addEventListener('DOMContentLoaded', initializeForm);
</script>
@endsection
