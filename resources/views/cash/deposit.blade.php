@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('cash.utilization') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Cash Utilization</a>
    <h1 class="text-3xl font-bold text-off-black">Deposit Cash to Bank</h1>
    <p class="text-black-50 mt-1">Transfer cash to your bank account</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card rounded-xl p-4 text-center">
        <p class="text-sm text-black-50">Available Cash</p>
        <p class="text-2xl font-bold text-green-400">₹{{ number_format($totalAvailable, 2) }}</p>
    </div>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('cash.deposit.store') }}" method="POST" id="depositForm">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-black-50 mb-1">Cash Source</label>
                <div class="flex gap-4 mb-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="cash_source_type" value="receipt" id="receiptRadio" checked
                            class="mr-2 accent-blue-500">
                        <span class="text-off-black">Select Individual Receipt</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="cash_source_type" value="lumpsum" id="lumpsumRadio"
                            class="mr-2 accent-blue-500">
                        <span class="text-off-black">Lumpsum (Combined)</span>
                    </label>
                </div>
            </div>

            <div id="receiptSelectContainer">
                <label class="block text-sm text-black-50 mb-1">Select Cash Receipt</label>
                <select name="cash_source_receipt_id" id="cash_source_receipt_id"
                    class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    <option value="">Select a cash receipt</option>
                    @foreach($availableReceipts as $receipt)
                        <option value="{{ $receipt->id }}" 
                            data-balance="{{ $receipt->balance }}"
                            data-amount="{{ $receipt->amount }}"
                            {{ old('cash_source_receipt_id') == $receipt->id ? 'selected' : '' }}>
                            {{ $receipt->buyer->name ?? 'N/A' }} - ₹{{ number_format($receipt->amount, 2) }} (Available: ₹{{ number_format($receipt->balance, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Amount to Deposit</label>
                    <input type="number" name="amount" id="amount" required step="0.01" min="0.01"
                        value="{{ old('amount') }}"
                        class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    <p class="text-gray-500 text-sm mt-1">Max: <span id="maxAmount">₹0.00</span></p>
                    @error('amount')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Date</label>
                    <input type="date" name="date" required value="{{ old('date', date('Y-m-d')) }}"
                        class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    @error('date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-1">Deposit Mode</label>
                <select name="mode" required
                    class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" {{ old('mode') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
                @error('mode')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm text-black-50 mb-1">Notes (Optional)</label>
                <textarea name="notes" rows="2"
                    class="w-full bg-gray-800 border border-oat-border rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none"
                    placeholder="Add any notes about this deposit...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('cash.utilization') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-off-black px-4 py-2 rounded-lg">Deposit</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiptSelect = document.getElementById('cash_source_receipt_id');
    const amountInput = document.getElementById('amount');
    const maxAmountSpan = document.getElementById('maxAmount');
    const receiptRadio = document.getElementById('receiptRadio');
    const lumpsumRadio = document.getElementById('lumpsumRadio');
    const receiptSelectContainer = document.getElementById('receiptSelectContainer');
    
    const totalAvailable = {{ $totalAvailable }};

    function updateMaxAmount() {
        if (receiptRadio.checked) {
            receiptSelectContainer.style.display = 'block';
            const selectedOption = receiptSelect.options[receiptSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const balance = parseFloat(selectedOption.dataset.balance);
                maxAmountSpan.textContent = '₹' + balance.toFixed(2);
                amountInput.max = balance;
                if (parseFloat(amountInput.value) > balance || parseFloat(amountInput.value) === 0) {
                    amountInput.value = balance.toFixed(2);
                }
            } else {
                maxAmountSpan.textContent = '₹0.00';
                amountInput.max = '';
            }
        } else {
            receiptSelectContainer.style.display = 'none';
            maxAmountSpan.textContent = '₹' + totalAvailable.toFixed(2);
            amountInput.max = totalAvailable;
            if (parseFloat(amountInput.value) > totalAvailable || parseFloat(amountInput.value) === 0) {
                amountInput.value = totalAvailable.toFixed(2);
            }
        }
    }

    receiptSelect.addEventListener('change', updateMaxAmount);
    receiptRadio.addEventListener('change', updateMaxAmount);
    lumpsumRadio.addEventListener('change', updateMaxAmount);

    updateMaxAmount();
});
</script>
@endsection
