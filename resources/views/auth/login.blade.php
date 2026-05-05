@extends('layouts.main')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="card p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2 text-center">CFH Fund Management</h1>
        <p class="text-slate-500 dark:text-slate-400 text-center mb-8">Sign in to continue</p>

        @if ($errors->any())
            <div class="bg-report-red/10 border border-report-red/50 text-report-red px-4 py-3 rounded-lg mb-6 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login', [], true) }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-slate-700 dark:text-slate-200 text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-fin-orange focus:border-transparent"
                    placeholder="admin@cfh.com" required autofocus>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-slate-700 dark:text-slate-200 text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" id="password"
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-fin-orange focus:border-transparent"
                    placeholder="Enter password" required>
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-curren text-fin-orange focus:ring-fin-orange focus:ring-offset-0">
                    <span class="ml-2 text-sm text-slate-700 dark:text-slate-200">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-primary w-full py-3 px-4">
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection