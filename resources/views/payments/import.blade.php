@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('payments.index') }}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 mb-2 inline-block">← Back to Payments</a>
    <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Import Payments</h1>
    <p class="text-slate-500 dark:text-slate-300">Import payments from text or file</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-white mb-4">Option 1: Copy & Paste</h2>
        
        <form action="{{ route('payments.import.preview') }}" method="POST">
            @csrf
            <input type="hidden" name="paste_mode" value="1">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Boat</label>
                    <select name="boat_id" id="pasteBoatSelect" required 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Landing (Optional)</label>
                    <select name="landing_id" id="pasteLandingSelect" 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Landing</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}" data-boat="{{ $landing->boat_id }}">
                                {{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Mode</label>
                        <select name="mode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($modes as $mode)
                                <option value="{{ $mode }}">{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Source</label>
                        <select name="source" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($sources as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Payment For</label>
                        <select name="payment_for" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($paymentFors as $pf)
                                <option value="{{ $pf }}">{{ $pf }}</option>
                            @endforeach
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
                <a href="{{ route('payments.index') }}" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-white mb-4">Option 2: Upload File</h2>
        
        <form action="{{ route('payments.import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="paste_mode" value="0">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">CSV File (.txt)</label>
                    <input type="file" name="csv_file" accept=".txt,.csv"
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-600/50 file:text-slate-700 dark:file:text-white file:border file:border-slate-300 dark:file:border-white/20 hover:file:bg-slate-200 dark:hover:file:bg-slate-500/60">
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Boat</label>
                    <select name="boat_id" id="fileBoatSelect" required 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Landing (Optional)</label>
                    <select name="landing_id" id="fileLandingSelect" 
                        class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Landing</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}" data-boat="{{ $landing->boat_id }}">
                                {{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Mode</label>
                        <select name="mode" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($modes as $mode)
                                <option value="{{ $mode }}">{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Source</label>
                        <select name="source" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($sources as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Payment For</label>
                        <select name="payment_for" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-white/80 dark:focus:bg-slate-700/40 focus:border-blue-500 dark:focus:border-blue-400/50 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/50 focus:outline-none">
                            @foreach($paymentFors as $pf)
                                <option value="{{ $pf }}">{{ $pf }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('pasteLandingSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const boatId = selected.dataset.boat;
    if (boatId) document.getElementById('pasteBoatSelect').value = boatId;
});

document.getElementById('fileLandingSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const boatId = selected.dataset.boat;
    if (boatId) document.getElementById('fileBoatSelect').value = boatId;
});
</script>
@endsection