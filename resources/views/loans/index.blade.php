@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Dashboard</a>
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-off-black">Outstanding Loans</h1>
            <p class="text-black-50 mt-1">Track loans taken from various sources</p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="openSourceModal()" class="bg-purple-600 hover:bg-purple-700 text-off-black px-4 py-2 rounded-lg">
                + Loan Sources
            </button>
            <a href="{{ route('loans.create') }}" class="bg-green-600 hover:bg-green-700 text-off-black px-4 py-2 rounded-lg">
                + Record New Loan
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="card rounded-xl p-4 border-l-4 border-red-500">
        <p class="text-black-50 text-xs">Total Outstanding</p>
        <p class="text-2xl font-bold text-red-400">Rs. {{ number_format($totalOutstanding, 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-purple-500">
        <p class="text-black-50 text-xs">Basheer</p>
        <p class="text-xl font-bold text-purple-400">Rs. {{ number_format($balances['Basheer'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-blue-500">
        <p class="text-black-50 text-xs">Personal</p>
        <p class="text-xl font-bold text-blue-400">Rs. {{ number_format($balances['Personal'], 2) }}</p>
    </div>
    <div class="card rounded-xl p-4 border-l-4 border-yellow-500">
        <p class="text-black-50 text-xs">Others</p>
        <p class="text-xl font-bold text-yellow-400">Rs. {{ number_format($balances['Others'], 2) }}</p>
    </div>
</div>

@if($totalOutstanding == 0)
<div class="card rounded-xl p-8 text-center">
    <svg class="w-16 h-16 mx-auto text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <h3 class="text-xl font-bold text-off-black dark:text-white mb-2">No Outstanding Loans</h3>
    <p class="text-black-50 dark:text-slate-400">All loans have been repaid. Great job managing your finances!</p>
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @foreach(['Basheer' => 'purple', 'Personal' => 'blue', 'Others' => 'yellow'] as $source => $color)
    <div class="card rounded-xl overflow-hidden">
        <div class="bg-{{ $color }}-900/30 dark:bg-{{ $color }}-900/50 px-6 py-3 border-b border-gray-700/50 dark:border-white/20">
            <div class="flex justify-between items-center">
                <h3 class="text-off-black font-medium">{{ $source }}</h3>
                <span class="text-{{ $color }}-400 font-bold">Rs. {{ number_format($balances[$source], 2) }}</span>
            </div>
        </div>
        <div class="p-4">
            @forelse($loansBySource[$source] as $loan)
                <div class="flex justify-between items-center py-2 border-b border-gray-700/30 dark:border-white/10 last:border-0">
                <div>
                    <p class="text-off-black dark:text-white">Rs. {{ number_format($loan->amount, 2) }}</p>
                    <p class="text-black-50 dark:text-slate-400 text-sm">{{ $loan->date->format('d M Y') }} via {{ $loan->mode }}</p>
                    @if($loan->repaid_amount > 0)
                        <p class="text-green-400 text-xs">Paid: Rs. {{ number_format($loan->repaid_amount, 2) }}</p>
                    @endif
                    @if($loan->notes)
                        <p class="text-gray-500 dark:text-slate-400 text-xs">{{ $loan->notes }}</p>
                    @endif
                </div>
                <button type="button" onclick="openRepayModal({{ $loan->id }}, '{{ $loan->source }}', {{ $loan->amount }}, {{ $loan->repaid_amount ?? 0 }})" class="bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-off-black px-3 py-1 rounded text-sm">
                    Repay
                </button>
            </div>
            @empty
                    <p class="text-gray-500 dark:text-slate-400 text-center py-4">No outstanding loans</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endif

<div id="repayModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-off-black mb-4">Repay Loan</h3>
        <form id="repayForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <p class="text-black-50 text-sm">Loan Source</p>
                    <p class="text-off-black font-medium" id="repaySource">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-black-50 text-sm">Total Amount</p>
                        <p class="text-off-black" id="repayTotal">Rs. 0.00</p>
                    </div>
                    <div>
                        <p class="text-black-50 text-sm">Already Repaid</p>
                        <p class="text-green-400" id="repayAlready">Rs. 0.00</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Repayment Amount *</label>
                    <input type="number" name="amount" id="repayAmount" required step="0.01" min="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Date *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Mode *</label>
                        <select name="mode" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="GP">GP</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-black-50 mb-1">Payment For *</label>
                    <select name="payment_for" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        @foreach($loanSources as $source)
                            <option value="{{ $source->name }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeRepayModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Repay</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentLoanId = null;

function openRepayModal(id, source, total, repaid) {
    currentLoanId = id;
    document.getElementById('repaySource').textContent = source;
    document.getElementById('repayTotal').textContent = 'Rs. ' + parseFloat(total).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('repayAlready').textContent = 'Rs. ' + parseFloat(repaid).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('repayAmount').max = total - repaid;
    document.getElementById('repayAmount').value = total - repaid;
    document.getElementById('repaySource').value = source;
    document.getElementById('repayForm').action = '/loans/' + id + '/repay';
    document.getElementById('repayModal').classList.remove('hidden');
    document.getElementById('repayModal').classList.add('flex');
}

function closeRepayModal() {
    document.getElementById('repayModal').classList.add('hidden');
    document.getElementById('repayModal').classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRepayModal();
});

function openSourceModal() {
    document.getElementById('sourceModal').classList.remove('hidden');
    document.getElementById('sourceModal').classList.add('flex');
}

function closeSourceModal() {
    document.getElementById('sourceModal').classList.add('hidden');
    document.getElementById('sourceModal').classList.remove('flex');
}
</script>

<div id="sourceModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-off-black mb-4">Add Loan Source</h3>
        <form method="POST" action="{{ route('loans.store-type') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-1">Source Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Bank, Agent" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                </div>
                <div class="bg-slate-50/40 dark:bg-slate-700/30 rounded-lg p-3">
                <p class="text-black-50 dark:text-slate-400 text-xs mb-2">Existing Sources:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($loanSources as $source)
                            <span class="bg-purple-900/50 text-purple-300 px-2 py-1 rounded text-xs">{{ $source->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeSourceModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-off-black px-4 py-2 rounded-lg">Add Source</button>
            </div>
        </form>
    </div>
</div>
@endsection