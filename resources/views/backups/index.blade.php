@extends('layouts.main')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-off-black mb-2">Database Backup & Restore</h1>
    <p class="text-black-50">Create and manage database backups</p>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-off-black">Local Backups</h2>
                <p class="text-sm text-black-50">Create and manage backups</p>
            </div>
        </div>

        <form action="{{ route('backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Backup
            </button>
        </form>

        <form action="{{ route('backups.clear') }}" method="POST" onsubmit="return confirm('WARNING: This will delete ALL data! Are you sure?')">
            @csrf
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Clear All Data
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
    </div>

    <div class="card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-off-black">Restore from File</h2>
                <p class="text-sm text-black-50">Upload a backup to restore</p>
            </div>
        </div>

        <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-off-black mb-2">Select Backup File (.sqlite)</label>
                <input type="file" name="backup_file" accept=".sqlite" required
                       class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-3 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-600 file:text-white file:cursor-pointer hover:file:bg-blue-700">
            </div>
            <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 px-4 py-3 rounded-lg text-sm mb-6">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <strong>Warning:</strong> Restoring a backup will replace all current data. 
                        A backup of your current database will be created automatically.
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Restore Database
            </button>
        </form>
    </div>
</div>
@endsection
