@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('payments.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Payments</a>
    <h1 class="text-3xl font-bold text-off-black">Edit Payment</h1>
</div>

<div class="card rounded-xl p-6 max-w-4xl">
    <form action="{{ route('payments.update', $payment) }}" method="POST" id="paymentForm">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat (Optional)</label>
                    <select name="boat_id" id="boat_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Boat (Optional)</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}" {{ $payment->boat_id == $boat->id ? 'selected' : '' }}>{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing (Optional)</label>
                    <select name="landing_id" id="landing_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Landing (Optional)</option>
                    </select>
                </div>
            </div>
            
            <div id="landingSummary" class="hidden card p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-black-50">Net Owner Payable</p>
                        <p class="text-off-black font-bold" id="netPayable">₹0.00</p>
                    </div>
                    <div>
                        <p class="text-black-50">Owner Pending</p>
                        <p class="text-yellow-400 font-bold" id="ownerPending">₹0.00</p>
                    </div>
                </div>
            </div>

            <div id="expensesSection" class="hidden">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-off-black font-semibold">Link Expenses to Payment</h3>
                    <button type="button" id="addExpenseBtn" class="text-sm bg-green-600 hover:bg-green-700 text-off-black px-3 py-1 rounded-lg">
                        + Add Expense
                    </button>
                </div>
                <div class="bg-slate-50/40 dark:bg-slate-700/30 rounded-lg p-4">
                    <div id="expensesList" class="space-y-3">
                        <p class="text-gray-500 text-sm">Select a boat to view pending expenses</p>
                    </div>
                    <div id="allocationTotal" class="hidden mt-4 pt-4 border-t border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="text-black-50">Total Allocated:</span>
                            <span class="text-off-black font-bold" id="totalAllocated">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-black-50">Payment Amount:</span>
                            <span class="text-off-black font-bold" id="paymentAmountDisplay">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-black-50">Remaining:</span>
                            <span class="font-bold" id="remainingAmount">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" required value="{{ $payment->date->format('Y-m-d') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount</label>
                    <input type="number" name="amount" id="amount" required step="0.01" min="0.01" placeholder="0.00" value="{{ $payment->amount }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Mode</label>
                    <select name="mode" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($modes as $mode)
                            <option value="{{ $mode }}" {{ $payment->mode == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Source</label>
                    <select name="source" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($sources as $source)
                            <option value="{{ $source }}" {{ $payment->source == $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Payment For</label>
                    <select name="payment_for" id="payment_for" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($paymentFors as $for)
                            <option value="{{ $for }}" {{ $payment->payment_for == $for ? 'selected' : '' }}>{{ $for }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div id="loanReferenceSection" class="{{ $payment->payment_for !== 'Loan' ? 'hidden' : '' }}">
                <label class="block text-sm text-black-50 mb-1">Loan Reference</label>
                <input type="text" name="loan_reference" id="loan_reference" placeholder="Bank name, loan type, etc." value="{{ $payment->loan_reference }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>

            <div id="vendorNameSection" class="{{ $payment->payment_for !== 'Other' ? 'hidden' : '' }}">
                <label class="block text-sm text-black-50 mb-1">Vendor Name <span class="text-red-400">*</span></label>
                <input type="text" name="vendor_name" id="vendor_name" placeholder="Enter the party name to whom payment is made" value="{{ $payment->vendor_name }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">{{ $payment->notes }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('payments.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update Payment</button>
        </div>
    </form>
</div>

<script>
let pendingExpenses = [];
let allocations = [];
const existingAllocations = @json($existingAllocations);
const currentLanding = @json($currentLanding);

function initializeForm() {
    const boatId = document.getElementById('boat_id').value;
    if (boatId) {
        loadBoatData(boatId, true);
    } else if (currentLanding && currentLanding.id) {
        document.getElementById('landing_id').innerHTML = `<option value="${currentLanding.id}">${currentLanding.date} - ₹${currentLanding.net_owner_payable} (${currentLanding.status})</option>`;
        document.getElementById('netPayable').textContent = '₹' + currentLanding.net_owner_payable;
        document.getElementById('ownerPending').textContent = '₹' + currentLanding.owner_pending;
        document.getElementById('landingSummary').classList.remove('hidden');
    }
}

function loadBoatData(boatId, preselectCurrent = false) {
    const landingSelect = document.getElementById('landing_id');
    const landingSummary = document.getElementById('landingSummary');
    const expensesSection = document.getElementById('expensesSection');
    
    landingSelect.innerHTML = '<option value="">Loading...</option>';
    landingSummary.classList.add('hidden');
    expensesSection.classList.add('hidden');
    allocations = [];
    
    if (boatId) {
        Promise.all([
            fetch(`/payments/landings/${boatId}`).then(r => r.json()),
            fetch(`/payments/expenses/${boatId}`).then(r => r.json())
        ]).then(([landingsData, expensesData]) => {
            landingSelect.innerHTML = '<option value="">Select Landing (Optional)</option>';
            landingsData.landings.forEach(landing => {
                const selected = preselectCurrent && currentLanding && currentLanding.id == landing.id ? 'selected' : '';
                landingSelect.innerHTML += `<option value="${landing.id}" ${selected}>${landing.date} - ₹${landing.net_owner_payable} (${landing.status})</option>`;
            });
            
            if (preselectCurrent && currentLanding && currentLanding.id) {
                const landingOption = landingSelect.querySelector(`option[value="${currentLanding.id}"]`);
                if (landingOption) {
                    landingOption.selected = true;
                    document.getElementById('netPayable').textContent = '₹' + currentLanding.net_owner_payable;
                    document.getElementById('ownerPending').textContent = '₹' + currentLanding.owner_pending;
                    landingSummary.classList.remove('hidden');
                }
            }
            
            pendingExpenses = expensesData.expenses;
            if (pendingExpenses.length > 0) {
                expensesSection.classList.remove('hidden');
                
                existingAllocations.forEach(existing => {
                    if (existing.type === 'expense') {
                        const exp = pendingExpenses.find(e => e.id === existing.id);
                        if (exp) {
                            allocations.push({
                                expense_id: existing.id,
                                amount: existing.amount,
                                _original: true
                            });
                        }
                    }
                });
                
                renderExpenseSelector();
            }
        });
    } else {
        landingSelect.innerHTML = '<option value="">Select Landing</option>';
    }
}

document.getElementById('boat_id').addEventListener('change', function() {
    loadBoatData(this.value, false);
});

document.getElementById('landing_id').addEventListener('change', function() {
    const landingId = this.value;
    const landingSummary = document.getElementById('landingSummary');
    
    if (landingId) {
        fetch(`/payments/landing/${landingId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('netPayable').textContent = '₹' + data.landing.net_owner_payable;
                document.getElementById('ownerPending').textContent = '₹' + data.landing.owner_pending;
                landingSummary.classList.remove('hidden');
            });
    } else {
        landingSummary.classList.add('hidden');
    }
});

document.getElementById('amount').addEventListener('input', updateAllocationSummary);

document.getElementById('addExpenseBtn').addEventListener('click', function() {
    showExpenseModal();
});

function renderExpenseSelector() {
    const list = document.getElementById('expensesList');
    
    if (allocations.length === 0) {
        list.innerHTML = `
            <p class="text-gray-500 text-sm mb-3">Click "Add Expense" to link expenses to this payment</p>
            <div class="space-y-2">
                ${pendingExpenses.map(exp => `
                    <div class="flex justify-between items-center p-3 bg-slate-100 dark:bg-slate-700/50 rounded-lg">
                        <div>
                            <span class="text-off-black font-medium">${exp.type}</span>
                            ${exp.vendor_name ? `<span class="text-black-50 text-sm ml-2">- ${exp.vendor_name}</span>` : ''}
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-400 font-bold">₹${exp.pending_amount}</span>
                            <span class="text-gray-500 text-xs ml-2">${exp.payment_status}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        return;
    }
    
    list.innerHTML = allocations.map((alloc, index) => {
        const exp = pendingExpenses.find(e => e.id === alloc.expense_id);
        const maxAmount = exp ? exp.pending_amount : 0;
        return `
            <div class="flex gap-3 items-center p-3 bg-slate-100 dark:bg-slate-700/50 rounded-lg allocation-row" data-index="${index}">
                <div class="flex-1">
                    <span class="text-off-black font-medium">${exp ? exp.type : 'Unknown'}</span>
                    ${exp && exp.vendor_name ? `<span class="text-black-50 text-sm ml-2">- ${exp.vendor_name}</span>` : ''}
                    <span class="text-yellow-400 text-sm ml-3">₹${maxAmount} pending</span>
                </div>
                <input type="number" 
                    class="allocation-amount w-32 bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white"
                    value="${alloc.amount}"
                    min="0.01"
                    max="${maxAmount}"
                    step="0.01"
                    placeholder="Amount"
                    data-index="${index}">
                <button type="button" onclick="removeAllocation(${index})" class="text-red-400 hover:text-red-300 px-2">
                    ✕
                </button>
                <input type="hidden" name="allocations[${index}][type]" value="expense">
                <input type="hidden" name="allocations[${index}][id]" value="${alloc.expense_id}">
                <input type="hidden" name="allocations[${index}][amount]" class="allocation-hidden" value="${alloc.amount}">
            </div>
        `;
    }).join('');
    
    document.querySelectorAll('.allocation-amount').forEach(input => {
        input.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            allocations[index].amount = parseFloat(this.value) || 0;
            document.querySelector(`.allocation-row[data-index="${index}"] .allocation-hidden`).value = allocations[index].amount;
            updateAllocationSummary();
        });
    });
    
    updateAllocationSummary();
}

function showExpenseModal() {
    const availableExpenses = pendingExpenses.filter(exp => 
        !allocations.some(a => a.expense_id === exp.id)
    );
    
    if (availableExpenses.length === 0) {
        alert('All expenses have been linked to this payment');
        return;
    }
    
    const modal = document.createElement('div');
    modal.id = 'expenseModal';
    modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-off-black text-lg font-semibold mb-4">Select Expense to Link</h3>
            <div class="space-y-2 max-h-80 overflow-y-auto">
                ${availableExpenses.map(exp => `
                    <button type="button" 
                        class="w-full text-left p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition expense-option"
                        data-id="${exp.id}"
                        data-type="${exp.type}"
                        data-vendor="${exp.vendor_name || ''}"
                        data-pending="${exp.pending_amount}">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-off-black font-medium">${exp.type}</span>
                                ${exp.vendor_name ? `<span class="text-black-50 text-sm ml-2">- ${exp.vendor_name}</span>` : ''}
                            </div>
                            <span class="text-yellow-400 font-bold">₹${exp.pending_amount}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelectorAll('.expense-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const expId = parseInt(this.dataset.id);
            const expType = this.dataset.type;
            const expVendor = this.dataset.vendor;
            const expPending = parseFloat(this.dataset.pending);
            
            const maxAmount = expPending;
            
            closeExpenseModal();
            
            const modal2 = document.createElement('div');
            modal2.id = 'amountModal';
            modal2.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50';
            modal2.innerHTML = `
                <div class="bg-gray-800 rounded-xl p-6 max-w-sm w-full mx-4">
                    <h3 class="text-off-black text-lg font-semibold mb-4">Enter Payment Amount</h3>
                    <p class="text-black-50 text-sm mb-4">${expType}${expVendor ? ' - ' + expVendor : ''}</p>
                    <p class="text-black-50 text-sm mb-2">Pending: <span class="text-yellow-400 font-bold">₹${expPending}</span></p>
                    <input type="number" id="expenseAmountInput" 
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-off-black mb-4"
                        placeholder="Enter amount"
                        min="0.01"
                        max="${maxAmount}"
                        step="0.01"
                        value="${maxAmount}">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeAmountModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                        <button type="button" onclick="confirmExpenseAllocation(${expId})" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Add</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal2);
            document.getElementById('expenseAmountInput').focus();
            document.getElementById('expenseAmountInput').select();
        });
    });
}

function closeExpenseModal() {
    const modal = document.getElementById('expenseModal');
    if (modal) modal.remove();
}

function closeAmountModal() {
    const modal = document.getElementById('amountModal');
    if (modal) modal.remove();
}

function confirmExpenseAllocation(expenseId) {
    const amountInput = document.getElementById('expenseAmountInput');
    const amount = parseFloat(amountInput.value);
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    
    const exp = pendingExpenses.find(e => e.id === expenseId);
    if (amount > exp.pending_amount) {
        alert('Amount cannot exceed pending amount');
        return;
    }
    
    allocations.push({ expense_id: expenseId, amount: amount });
    closeAmountModal();
    renderExpenseSelector();
}

function removeAllocation(index) {
    allocations.splice(index, 1);
    renderExpenseSelector();
}

function updateAllocationSummary() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const totalAllocated = allocations.reduce((sum, a) => sum + a.amount, 0);
    const remaining = amount - totalAllocated;
    
    const totalDiv = document.getElementById('allocationTotal');
    const remainingSpan = document.getElementById('remainingAmount');
    
    if (allocations.length > 0) {
        totalDiv.classList.remove('hidden');
        document.getElementById('totalAllocated').textContent = '₹' + totalAllocated.toFixed(2);
        document.getElementById('paymentAmountDisplay').textContent = '₹' + amount.toFixed(2);
        remainingSpan.textContent = '₹' + remaining.toFixed(2);
        remainingSpan.className = remaining < 0 ? 'text-red-400 font-bold' : remaining === 0 ? 'text-green-400 font-bold' : 'text-yellow-400 font-bold';
    } else {
        totalDiv.classList.add('hidden');
    }
}

function toggleLoanReference() {
    const paymentFor = document.getElementById('payment_for').value;
    const loanSection = document.getElementById('loanReferenceSection');
    if (paymentFor === 'Loan') {
        loanSection.classList.remove('hidden');
    } else {
        loanSection.classList.add('hidden');
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

document.getElementById('payment_for').addEventListener('change', toggleLoanReference);
document.getElementById('payment_for').addEventListener('change', toggleVendorName);

document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    toggleLoanReference();
    toggleVendorName();
});
</script>
@endsection
