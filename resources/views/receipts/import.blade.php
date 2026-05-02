@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('receipts.index') }}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 mb-2 inline-block">← Back to Receipts</a>
    <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Import Receipts</h1>
    <p class="text-slate-500 dark:text-slate-300">Import receipts from text or file</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-white mb-4">Option 1: Copy & Paste</h2>
        
        <form action="{{ route('receipts.import.preview') }}" method="POST">
            @csrf
            <input type="hidden" name="paste_mode" value="1">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Buyer Name</label>
                    <select name="buyer_name" required
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Buyer</option>
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->name }}">{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Invoice</label>
                    <select name="invoice_id" required
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->buyer->name ?? 'Unknown' }} - {{ $invoice->invoice_date->format('Y-m-d') }} - ₹{{ number_format($invoice->pending_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Mode</label>
                        <select name="mode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="GP">GP</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Paste Data</label>
                    <textarea name="paste_data" rows="8" placeholder="1500.00" 
                        class="w-full bg-white/50 dark:bg-slate-700/30 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none resize-none font-mono text-sm"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Format: One amount per line</p>
                    <pre class="text-xs text-slate-200 bg-slate-800/50 border border-white/15 p-2 rounded mt-1 font-mono">1500.00
2500.00</pre>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('receipts.index') }}" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-white mb-4">Option 2: Upload File</h2>
        
        <form action="{{ route('receipts.import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="paste_mode" value="0">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">CSV File (.txt)</label>
                    <input type="file" name="csv_file" accept=".txt,.csv"
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-600/50 file:text-slate-700 dark:file:text-white file:border file:border-slate-300 dark:file:border-white/20 hover:file:bg-slate-200 dark:hover:file:bg-slate-500/60">
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Buyer Name</label>
                    <select name="buyer_name" required
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Buyer</option>
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->name }}">{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Invoice</label>
                    <select name="invoice_id" required
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->buyer->name ?? 'Unknown' }} - {{ $invoice->invoice_date->format('Y-m-d') }} - ₹{{ number_format($invoice->pending_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Mode</label>
                        <select name="mode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="GP">GP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('receipts.index') }}" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection