@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-off-black mb-2">Control Panel</h1>
    <p class="text-black-50">Generate reports and manage database backups</p>
</div>

@if(session('success'))
    <div class="card p-4 mb-6 border-l-4 border-green-500 text-green-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="card p-4 mb-6 border-l-4 border-red-500 text-red-400">
        {{ session('error') }}
    </div>
@endif

<div class="card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-off-black">Fund Flow Report</h2>
            <p class="text-sm text-black-50">Category-wise inflows and outflows with PDF/Excel export</p>
        </div>
    </div>

    <a href="{{ route('reports.fund-flow') }}" 
       class="w-full block text-center bg-purple-600 hover:bg-purple-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        View Fund Flow Report
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-off-black">Settlement Reports</h2>
                <p class="text-sm text-black-50">Generate PDF settlement reports</p>
            </div>
        </div>

        <form action="{{ route('reports.generate') }}" method="GET" id="reportForm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-black-50 mb-2">Select Boat</label>
                    <select name="boat_id" id="boat_id" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-3 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">-- Select Boat --</option>
                        @forelse($boats as $boat)
                            <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                        @empty
                            <option value="" disabled>No boats available</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-black-50 mb-2">Select Landing (Optional)</label>
                    <select name="landing_id" id="landing_id" class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-3 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                        <option value="">-- All Landings --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave empty to generate report for all landings</p>
                </div>
            </div>

            <button type="submit" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Generate PDF Report
            </button>
        </form>
    </div>

    <div class="card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-off-black">Database Backup</h2>
                <p class="text-sm text-black-50">Create and restore backups</p>
            </div>
        </div>

        <form action="{{ route('backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2 mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Backup
            </button>
        </form>

        @if(count($backups) > 0)
            <div class="space-y-2">
                <h3 class="text-sm font-medium text-black-50 mb-3">Existing Backups</h3>
                @foreach($backups as $backup)
                <div class="flex items-center justify-between bg-gray-800/30 rounded-lg px-4 py-3">
                    <div>
                        <p class="text-off-black text-sm">{{ $backup['filename'] }}</p>
                        <p class="text-gray-500 text-xs">{{ number_format($backup['size'] / 1024, 1) }} KB • {{ date('M d, H:i', $backup['modified']) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('backups.download', $backup['filename']) }}" 
                           class="text-blue-400 hover:text-blue-300 p-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </a>
                        <form action="{{ route('backups.destroy', $backup['filename']) }}" method="POST" class="inline" 
                              onsubmit="return confirm('Delete this backup?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 p-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm text-center py-4">No backups found</p>
        @endif

        <div class="mt-6 pt-6 border-t border-gray-700">
            <h3 class="text-sm font-medium text-black-50 mb-3">Restore from File</h3>
            <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="backup_file" accept=".sqlite" required
                           class="w-full bg-gray-800/50 border border-gray-600 rounded-lg px-4 py-2 text-off-black text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-600 file:text-off-black file:cursor-pointer hover:file:bg-blue-700">
                </div>
                <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 px-3 py-2 rounded-lg text-xs mb-3">
                    Warning: This will replace all current data
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Restore Database
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const boatSelect = document.getElementById('boat_id');
    const landingSelect = document.getElementById('landing_id');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    boatSelect.addEventListener('change', function() {
        const boatId = this.value;
        landingSelect.innerHTML = '<option value="">-- All Landings --</option>';

        if (!boatId) return;

        fetch(`/invoices/landings/${boatId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            landingSelect.innerHTML = '<option value="">-- All Landings --</option>';
            data.forEach(landing => {
                const option = document.createElement('option');
                option.value = landing.id;
                option.textContent = `${landing.date} - Rs.${parseFloat(landing.invoice_total || 0).toLocaleString()}`;
                landingSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading landings:', error);
            landingSelect.innerHTML = '<option value="">Error loading</option>';
        });
    });
});
</script>
@endpush
