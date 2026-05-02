@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('users.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Users</a>
    <h1 class="text-3xl font-bold text-off-black">Edit User</h1>
</div>

<div class="card rounded-xl p-6 max-w-2xl">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-black-50 mb-1">Name</label>
                <input type="text" name="name" value="{{ $user->name }}" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" minlength="8" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" minlength="8" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-black-50 mb-1">Role</label>
                <select name="role" required class="w-full bg-white/60 dark:bg-slate-700/50 border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white rounded-lg px-4 py-2 focus:border-blue-500 dark:focus:border-blue-400/50 focus:outline-none">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            @if(auth()->id() !== $user->id)
            <div>
                <label class="block text-sm text-black-50 mb-1">Status</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="w-4 h-4 text-green-500 bg-gray-700 border-gray-600 focus:ring-green-500">
                        <span class="text-off-black">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ !$user->is_active ? 'checked' : '' }} class="w-4 h-4 text-red-500 bg-gray-700 border-gray-600 focus:ring-red-500">
                        <span class="text-off-black">Inactive</span>
                    </label>
                </div>
            </div>
            @endif
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('users.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">Update User</button>
        </div>
    </form>
</div>
@endsection
