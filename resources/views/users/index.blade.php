@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-off-black">User Management</h1>
        <p class="text-black-50">Manage system users and permissions</p>
    </div>
    <div class="flex gap-3">
        <button onclick="document.getElementById('clearDataModal').classList.remove('hidden')" class="bg-orange-600 hover:bg-orange-700 text-off-black px-4 py-2 rounded-lg">
            Clear Data
        </button>
        <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
            + Add User
        </a>
    </div>
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

<div class="card rounded-xl overflow-hidden">
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-50/60 dark:bg-slate-700/50">
            <tr class="text-black-50 text-sm">
                <th class="text-left px-6 py-4">Name</th>
                <th class="text-left px-6 py-4">Email</th>
                <th class="text-center px-6 py-4">Role</th>
                <th class="text-center px-6 py-4">Status</th>
                <th class="text-left px-6 py-4">Created</th>
                <th class="text-center px-6 py-4">Records</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="border-t border-gray-700/50 hover:bg-white/5">
                <td class="px-6 py-4 font-medium text-off-black">{{ $user->name }}</td>
                <td class="px-6 py-4 text-black-50">{{ $user->email }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 rounded text-xs 
                        @if($user->role === 'admin') bg-purple-500/20 text-purple-400
                        @else bg-blue-500/20 text-blue-400 @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($user->is_active)
                        <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Active</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-black-50">{{ $user->created_at->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-off-black">{{ $user->data_count }} records</span>
                </td>
                <td class="px-6 py-4 text-center">
                    <a href="{{ route('users.edit', $user) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                    @if(auth()->id() !== $user->id)
                        <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="inline mr-3">
                            @csrf
                            <button type="submit" class="{{ $user->is_active ? 'text-yellow-400 hover:text-yellow-300' : 'text-green-400 hover:text-green-300' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @if($user->data_count > 0)
                            <button onclick="showClearUserModal({{ $user->id }}, '{{ $user->name }}', {{ $user->data_count }})" class="text-orange-400 hover:text-orange-300 mr-3">Clear Data</button>
                        @endif
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    @else
                        <span class="text-gray-500 text-sm">Current User</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="clearDataModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Clear All Data</h3>
            <button onclick="document.getElementById('clearDataModal').classList.add('hidden')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-black-50 mb-4">This will permanently delete all business data:</p>
        <ul class="text-off-black text-sm mb-4 space-y-1">
            <li>• Landings</li>
            <li>• Invoices</li>
            <li>• Receipts</li>
            <li>• Expenses</li>
            <li>• Payments</li>
            <li>• Boats</li>
            <li>• Buyers</li>
            <li>• Loans</li>
        </ul>
        <p class="text-green-400 text-sm mb-4">User accounts will be preserved.</p>
        <form action="{{ route('backups.clear') }}" method="POST">
            @csrf
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('clearDataModal').classList.add('hidden')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-off-black px-4 py-2 rounded-lg">Clear Data</button>
            </div>
        </form>
    </div>
</div>

<div id="clearUserDataModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Clear User Data</h3>
            <button onclick="document.getElementById('clearUserDataModal').classList.add('hidden')" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-black-50 mb-4">This will permanently delete all business data for:</p>
        <p class="text-off-black font-medium mb-4" id="clearUserName"></p>
        <p class="text-black-50 mb-4" id="clearUserCount"></p>
        <p class="text-orange-400 text-sm mb-4">User account will be preserved. This action cannot be undone.</p>
        <form action="#" method="POST" id="clearUserForm">
            @csrf
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('clearUserDataModal').classList.add('hidden')" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-off-black px-4 py-2 rounded-lg">Clear User Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function showClearUserModal(userId, userName, recordCount) {
    document.getElementById('clearUserName').textContent = userName;
    document.getElementById('clearUserCount').textContent = recordCount + ' records will be deleted.';
    document.getElementById('clearUserForm').action = '/backups/clear-user/' + userId;
    document.getElementById('clearUserDataModal').classList.remove('hidden');
}
</script>
@endsection
