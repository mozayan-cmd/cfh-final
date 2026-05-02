@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('invoices.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Invoices</a>
    <h1 class="text-3xl font-bold text-white">Import Invoices</h1>
    <p class="text-slate-300">Import invoices from text or file</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Option 1: Copy & Paste</h2>
        
        <form action="{{ route('invoices.preview') }}" method="POST">
            @csrf
            <input type="hidden" name="paste_mode" value="1">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Boat</label>
                    <select name="boat_id" id="pasteBoatSelect" required 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Landing</label>
                    <select name="landing_id" id="pasteLandingSelect" required 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Landing</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Select a landing to link the imported invoices to</p>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" required value="{{ date('Y-m-d') }}" 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Paste Data</label>
                    <textarea name="paste_data" rows="8" placeholder="John Doe|1500.00&#10;Jane Smith|2500.00" 
                        class="w-full bg-slate-700/30 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none resize-none font-mono text-sm"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Format: Buyer Name|Amount (one per line)</p>
                    <p class="text-xs text-slate-400 mt-1">Example:</p>
                    <pre class="text-xs text-slate-200 bg-slate-800/50 border border-white/15 p-2 rounded mt-1 font-mono">John Doe|1500.00
Jane Smith|2500.00</pre>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Option 2: Upload File</h2>
        
        <form action="{{ route('invoices.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="paste_mode" value="0">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Boat</label>
                    <select name="boat_id" id="fileBoatSelect" required 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Boat</option>
                        @foreach($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Landing</label>
                    <select name="landing_id" id="fileLandingSelect" required 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                        <option value="" class="text-slate-400">Select Landing</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" required value="{{ date('Y-m-d') }}" 
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Import File (.txt)</label>
                    <input type="file" name="file" accept=".txt"
                        class="w-full bg-slate-700/50 border border-white/20 text-white placeholder:text-slate-400 rounded-lg px-4 py-2.5 focus:bg-slate-700/40 focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-600/50 file:text-white file:border file:border-white/20 hover:file:bg-slate-500/60">
                    <p class="text-xs text-slate-400 mt-1">Format: Buyer Name|Amount (pipe separated)</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('pasteBoatSelect').addEventListener('change', function() {
    loadLandings(this.value, 'pasteLandingSelect');
});

document.getElementById('fileBoatSelect').addEventListener('change', function() {
    loadLandings(this.value, 'fileLandingSelect');
});

function loadLandings(boatId, selectId) {
    const landingSelect = document.getElementById(selectId);
    landingSelect.innerHTML = '<option value="" class="text-slate-400">Loading...</option>';
    
    if (!boatId) {
        landingSelect.innerHTML = '<option value="" class="text-slate-400">Select Landing</option>';
        return;
    }
    
    fetch(`/invoices/landings/${boatId}`)
        .then(response => response.json())
        .then(data => {
            landingSelect.innerHTML = '<option value="" class="text-slate-400">Select Landing</option>';
            data.forEach(landing => {
                const date = new Date(landing.date).toLocaleDateString('en-GB');
                landingSelect.innerHTML += `<option value="${landing.id}">${date} (${landing.status})</option>`;
            });
        })
        .catch(error => {
            landingSelect.innerHTML = '<option value="">Error loading landings</option>';
        });
}
</script>
@endsection