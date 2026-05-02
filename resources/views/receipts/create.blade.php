@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('receipts.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Receipts</a>
    <h1 class="text-3xl font-bold text-off-black">New Receipt</h1>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('receipts.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Boat</label>
                    <select id="boat_id" name="boat_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing Date</label>
                    <select id="landing_id" name="landing_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">Select Landing Date</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}" data-boat="{{ $landing->boat_id }}">{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Buyer</label>
                <select id="buyer_id" name="buyer_id" required disabled class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none disabled:opacity-50">
                    <option value="">Select Buyer</option>
                </select>
                <p id="buyer_info" class="text-xs text-gray-500 mt-1">Select boat and landing date first</p>
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Invoice</label>
                <select id="invoice_id" name="invoice_id" required disabled class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none disabled:opacity-50">
                    <option value="">Select Invoice</option>
                </select>
                <p id="invoice_info" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount</label>
                    <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0.00" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Mode</label>
                    <select name="mode" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($modes as $mode)
                            <option value="{{ $mode }}">{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('receipts.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Record Receipt</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const boatSelect = document.getElementById('boat_id');
    const landingSelect = document.getElementById('landing_id');
    const buyerSelect = document.getElementById('buyer_id');
    const invoiceSelect = document.getElementById('invoice_id');
    const buyerInfo = document.getElementById('buyer_info');
    const invoiceInfo = document.getElementById('invoice_info');

    const landingOptions = Array.from(landingSelect.querySelectorAll('option[data-boat]'));

    boatSelect.addEventListener('change', function() {
        const boatId = this.value;
        
        landingSelect.value = '';
        buyerSelect.innerHTML = '<option value="">Select Buyer</option>';
        buyerSelect.disabled = true;
        invoiceSelect.innerHTML = '<option value="">Select Invoice</option>';
        invoiceSelect.disabled = true;
        buyerInfo.textContent = 'Select boat and landing date first';
        invoiceInfo.textContent = '';

        if (boatId) {
            landingSelect.disabled = false;
            landingSelect.innerHTML = '<option value="">Select Landing Date</option>';
            
            landingOptions.forEach(option => {
                if (option.dataset.boat === boatId) {
                    landingSelect.appendChild(option.cloneNode(true));
                }
            });
        } else {
            landingSelect.disabled = true;
        }
    });

    landingSelect.addEventListener('change', function() {
        const boatId = boatSelect.value;
        const landingId = this.value;
        
        buyerSelect.innerHTML = '<option value="">Loading...</option>';
        buyerSelect.disabled = true;
        invoiceSelect.innerHTML = '<option value="">Select Invoice</option>';
        invoiceSelect.disabled = true;
        invoiceInfo.textContent = '';

        if (landingId && boatId) {
            fetch(`/receipts/api/buyers?boat_id=${boatId}&landing_id=${landingId}`)
                .then(response => response.json())
                .then(data => {
                    buyerSelect.innerHTML = '<option value="">Select Buyer</option>';
                    if (data.buyers.length === 0) {
                        buyerInfo.textContent = 'No buyers with pending invoices for this landing';
                    } else {
                        buyerInfo.textContent = `${data.buyers.length} buyer(s) found`;
                        data.buyers.forEach(buyer => {
                            buyerSelect.innerHTML += `<option value="${buyer.id}">${buyer.name}</option>`;
                        });
                        buyerSelect.disabled = false;
                    }
                });
        } else {
            buyerInfo.textContent = 'Select boat and landing date first';
        }
    });

    buyerSelect.addEventListener('change', function() {
        const buyerId = this.value;
        const boatId = boatSelect.value;
        const landingId = landingSelect.value;
        
        invoiceSelect.innerHTML = '<option value="">Loading...</option>';
        invoiceSelect.disabled = true;
        invoiceInfo.textContent = '';

        if (buyerId && landingId) {
            const url = `/receipts/api/invoices?buyer_id=${buyerId}&boat_id=${boatId}&landing_id=${landingId}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    invoiceSelect.innerHTML = '<option value="">Select Invoice</option>';
                    if (data.invoices.length === 0) {
                        invoiceInfo.textContent = 'No pending invoices for this buyer';
                    } else {
                        data.invoices.forEach(invoice => {
                            invoiceSelect.innerHTML += `<option value="${invoice.id}" data-pending="${invoice.pending_amount}">${invoice.invoice_date} - ₹${invoice.pending_amount} pending</option>`;
                        });
                        invoiceSelect.disabled = false;
                    }
                });
        }
    });

    invoiceSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const amountInput = document.querySelector('input[name="amount"]');
        
        if (selected && selected.dataset.pending) {
            invoiceInfo.textContent = `Pending amount: ₹${selected.dataset.pending}`;
            amountInput.max = selected.dataset.pending;
            amountInput.placeholder = `Max: ₹${selected.dataset.pending}`;
        } else {
            invoiceInfo.textContent = '';
            amountInput.removeAttribute('max');
            amountInput.placeholder = '0.00';
        }
    });
});
</script>
@endsection
