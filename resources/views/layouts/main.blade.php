<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CFH Fund Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'off-black': '#111111',
                        'warm-cream': '#faf9f6',
                        'fin-orange': '#ff5600',
                        'report-orange': '#fe4c02',
                        'oat-border': '#dedbd6',
                        'warm-sand': '#d3cec6',
                        'black-80': '#313130',
                        'black-60': '#626260',
                        'black-50': '#7b7b78',
                        'content-tertiary': '#9c9fa5',
                        'report-blue': '#65b5ff',
                        'report-green': '#0bdf50',
                        'report-red': '#c41c1c',
                        'report-pink': '#ff2067',
                        'report-lime': '#b3e01c',
                    }
                }
            }
        }
    </script>
    <script>
        // Theme initialization - run BEFORE page renders to prevent FOUC
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.add(savedTheme);
        })();
    </script>
    <style>
        /* Base styles (light mode by default) */
        body {
            background: linear-gradient(to bottom right, #f8fafc, #e0f2fe, #f1f5f9);
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            color: #0f172a;
            transition: background-color 0.2s, color 0.2s;
        }
        /* Dark mode - applied when html has .dark class */
        html.dark body {
            background: #0f172a;
            color: #ffffff;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s, border-color 0.2s;
        }
        html.dark .card {
            background: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        /* Dark mode sidebar */
        html.dark aside {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark aside h1 {
            color: #ffffff !important;
        }
        html.dark aside p {
            color: #94a3b8 !important;
        }
        html.dark .sidebar-link {
            color: #ffffff !important;
        }
        html.dark .sidebar-link:hover {
            background: #334155 !important;
        }
        html.dark .sidebar-link.active {
            background: #1e3a8a !important;
            border-left: 3px solid #ff5600 !important;
        }
        /* Dark mode user section */
        html.dark .border-t {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        /* Light mode card (legacy - now default) */
        .light .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Light mode user section */
        .light .border-t, body.light-mode .border-t {
            border-color: rgba(203, 213, 225, 0.6) !important;
        }
        .light .text-white {
            color: #0f172a !important;
        }
/* Light mode inputs */
        .light input, .light select, .light textarea {
            background: rgba(255, 255, 255, 0.6) !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        .light input::placeholder {
            color: #94a3b8 !important;
        }
        /* Light mode form base overrides for non-dark mode */
        input, select, textarea {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 4px;
            padding: 12px 16px;
            transition: border-color 200ms ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #2563eb;
            outline: 2px solid #2563eb;
            outline-offset: 0;
        }
        input::placeholder {
            color: #94a3b8;
        }
/* Dark mode input overrides - target html.dark AND body.dark */
        html.dark input, html.dark select, html.dark textarea,
        body.dark input, body.dark select, body.dark textarea {
            background: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }
        /* Dark mode select dropdown options */
        html.dark select option, body.dark select option {
            background: #1e293b !important;
            color: #ffffff !important;
        }
        html.dark input::placeholder, body.dark input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus,
        body.dark input:focus, body.dark select:focus, body.dark textarea:focus {
            border-color: #60a5fa !important;
            outline: 2px solid #60a5fa !important;
        }
        
        /* Login page dark mode fixes */
        html.dark .min-h-screen {
            background: #0f172a !important;
        }
        html.dark .min-h-screen .card {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark .min-h-screen input,
        html.dark .min-h-screen select,
        html.dark .min-h-screen textarea {
            background: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }
        html.dark .min-h-screen input::placeholder,
        html.dark .min-h-screen textarea::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        html.dark .min-h-screen .text-slate-800,
        html.dark .min-h-screen .text-slate-700,
        html.dark .min-h-screen .text-slate-500,
        html.dark .min-h-screen .text-black-50,
        html.dark .min-h-screen label {
            color: #e2e8f0 !important;
        }
        html.dark .min-h-screen .btn-primary {
            background: #2563eb !important;
            color: #ffffff !important;
        }
        .text-slate-300 {
            color: #475569 !important;
        }
        .text-slate-200 {
            color: #334155 !important;
        }
        .text-slate-500 {
            color: #475569 !important;
        }
        .text-black-50 {
            color: #475569 !important;
        }
        .text-content-tertiary {
            color: #64748b !important;
        }
        /* Dark mode text - restore lighter shades */
        html.dark .text-slate-400 {
            color: #94a3b8 !important;
        }
        html.dark .text-slate-300 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-200 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-500 {
            color: #94a3b8 !important;
        }
        html.dark .text-black-50 {
            color: #94a3b8 !important;
        }
        html.dark .text-content-tertiary {
            color: #94a3b8 !important;
        }
        /* Fix text-off-black in dark mode */
        html.dark .text-off-black {
            color: #ffffff !important;
        }
        html.dark input::placeholder, body.dark input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus,
        body.dark input:focus, body.dark select:focus, body.dark textarea:focus {
            border-color: #60a5fa !important;
            outline: 2px solid #60a5fa !important;
        }
        
        /* Login page dark mode fixes */
        html.dark .min-h-screen {
            background: #0f172a !important;
        }
        html.dark .min-h-screen .card {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
        html.dark .min-h-screen input,
        html.dark .min-h-screen select,
        html.dark .min-h-screen textarea {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
        }
        html.dark .min-h-screen input::placeholder,
        html.dark .min-h-screen textarea::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        html.dark .min-h-screen .text-slate-800,
        html.dark .min-h-screen .text-slate-700,
        html.dark .min-h-screen .text-slate-500,
        html.dark .min-h-screen .text-black-50,
        html.dark .min-h-screen label {
            color: #e2e8f0 !important;
        }
        html.dark .min-h-screen .btn-primary {
            background: #2563eb !important;
            color: #ffffff !important;
        }
        .light input::placeholder {
            color: #94a3b8 !important;
        }
        /* Light mode scrollbar */
        * {
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        *::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        *::-webkit-scrollbar-thumb {
            background: #94a3b8;
        }
        *::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        *::-webkit-scrollbar-corner {
            background: #f1f5f9;
        }
        /* Dark mode scrollbar */
        html.dark * {
            scrollbar-color: #475569 #1e293b;
        }
        html.dark *::-webkit-scrollbar-track {
            background: #1e293b;
        }
        html.dark *::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
            border: 2px solid #1e293b;
        }
        html.dark *::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        html.dark *::-webkit-scrollbar-corner {
            background: #1e293b;
        }
        html.dark *::-webkit-scrollbar-track {
            background: #1e293b;
        }
        html.dark *::-webkit-scrollbar-thumb {
            background: rgba(100, 100, 100, 0.4);
        }
        html.dark *::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 100, 100, 0.6);
        }
        html.dark *::-webkit-scrollbar-corner {
            background: #1e293b;
        }
        .light *::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .light *::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
        }
        .sidebar-link {
            transition: all 0.2s ease;
            border-radius: 4px;
        }
        .dark .sidebar-link:hover {
            background: #334155 !important;
        }
        .dark .sidebar-link.active {
            background: #1e3a8a !important;
            border-left: 3px solid #ff5600;
        }
        .light .sidebar-link:hover {
            background: #e2e8f0 !important;
        }
        .light .sidebar-link.active {
            background: #dbeafe !important;
            border-left: 3px solid #2563eb !important;
        }
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-container table {
            min-width: 600px;
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 100, 100, 0.4) #1e293b;
        }
        *::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        *::-webkit-scrollbar-track {
            background: #1e293b;
        }
        *::-webkit-scrollbar-thumb {
            background: rgba(100, 100, 100, 0.4);
            border-radius: 4px;
            border: 2px solid #1e293b;
        }
        *::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 100, 100, 0.6);
        }
        *::-webkit-scrollbar-corner {
            background: #1e293b;
        }
        .btn-primary {
            background: #111111;
            color: #ffffff;
            border-radius: 4px;
            padding: 0px 14px;
            font-weight: 500;
            transition: all 200ms ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover {
            background: #ffffff;
            color: #111111;
            transform: scale(1.1);
        }
        .btn-primary:active {
            background: #2c6415;
            color: #ffffff;
            transform: scale(0.85);
        }
        .btn-primary:focus {
            outline: 2px solid #ff5600;
            outline-offset: 2px;
        }
        /* Collapsed sidebar states */
        .sidebar-collapsed aside {
            width: 5rem !important;
        }
        .sidebar-collapsed .sidebar-header-text {
            display: none !important;
        }
        .sidebar-collapsed .sidebar-nav a {
            justify-content: center;
            padding: 0.75rem;
        }
        .sidebar-collapsed .sidebar-nav a span,
        .sidebar-collapsed .sidebar-nav a .nav-label {
            display: none;
        }
        .sidebar-collapsed .sidebar-user {
            flex-direction: column;
            gap: 0.5rem;
        }
        .sidebar-collapsed .sidebar-user > div:first-child {
            justify-content: center;
        }
        .sidebar-collapsed .sidebar-user-text {
            display: none;
        }
        .sidebar-collapsed .sidebar-logout {
            padding: 0.5rem;
        }
        .sidebar-collapsed .sidebar-logout span,
        .sidebar-collapsed .sidebar-logout .logout-text {
            display: none;
        }
        .sidebar-collapsed .sidebar-theme-btn span,
        .sidebar-collapsed .sidebar-theme-btn .theme-label {
            display: none;
        }
        /* Main content adjustment */
        .main-expanded {
            margin-left: 16rem;
        }
        .main-collapsed {
            margin-left: 5rem;
        }
        /* Toggle icon rotation */
        .sidebar-collapsed #sidebarToggleIcon {
            transform: rotate(180deg);
        }
        /* Main content margin adjustment */
        body.main-collapsed main {
            margin-left: 5rem !important;
        }
        .btn-secondary {
            background: transparent;
            color: #111111;
            border: 1px solid #111111;
            border-radius: 4px;
            padding: 0px 14px;
            font-weight: 500;
            transition: all 200ms ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: #111111;
            color: #ffffff;
            transform: scale(1.1);
        }
        .btn-secondary:active {
            background: #2c6415;
            color: #ffffff;
            transform: scale(0.85);
        }
        .btn-secondary:focus {
            outline: 2px solid #ff5600;
            outline-offset: 2px;
        }
        input, select, textarea {
            background: #ffffff;
            border: 1px solid #dedbd6;
            color: #111111;
            border-radius: 4px;
            padding: 12px 16px;
            transition: border-color 200ms ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #ff5600;
            outline: 2px solid #ff5600;
            outline-offset: 0;
        }
        input::placeholder {
            color: #7b7b78;
        }
    </style>
</head>
<body class="overflow-x-hidden">
    <div class="flex h-screen overflow-hidden">
        <aside class="fixed left-0 top-0 h-screen w-64 card border-r border-slate-200 dark:border-white/10 flex flex-col bg-white/80 dark:bg-white/5 transition-all duration-300">
            <div class="p-6 border-b border-slate-200 dark:border-white/10 flex-shrink-0 flex items-center justify-between">
                <div class="sidebar-header-text">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">CFH</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 sidebar-header-text">Fund Management</p>
                </div>
                <button id="sidebarToggle" type="button" aria-label="Toggle sidebar" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-all duration-200">
                    <svg id="sidebarToggleIcon" class="w-5 h-5 text-slate-600 dark:text-slate-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>
            <nav class="p-4 space-y-1 flex-1 overflow-y-auto sidebar-nav">
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="{{ route('boats.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('boats.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="nav-label">Boats</span>
                </a>
                <a href="{{ route('landings.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('landings.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="nav-label">Landings</span>
                </a>
                <a href="{{ route('buyers.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('buyers.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="nav-label">Buyers</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="nav-label">Invoices</span>
                </a>
                <a href="{{ route('invoices.import') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('invoices.import') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span class="nav-label">Import Invoices</span>
                </a>
                <a href="{{ route('receipts.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('receipts.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="nav-label">Receipts</span>
                </a>
                <a href="{{ route('expenses.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="nav-label">Expenses</span>
                </a>
                <a href="{{ route('payments.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span class="nav-label">Payments</span>
                </a>
                <a href="{{ route('cash.utilization') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('cash.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="nav-label">Cash Management</span>
                </a>
                <a href="{{ route('loans.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="nav-label">Loans</span>
                </a>
                <a href="{{ route('bank.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('bank.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="nav-label">Bank Management</span>
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('reports.*') || request()->routeIs('backups.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="nav-label">Control Panel</span>
                </a>
                @auth
                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="nav-label">User Management</span>
                </a>
                <a href="{{ route('unlinked-expenses.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-white {{ request()->routeIs('unlinked-expenses.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    <span class="nav-label">Unlinked Expenses</span>
                </a>
                @endif
                @endauth
            </nav>
            <div class="p-4 border-t border-slate-200 dark:border-white/10 flex-shrink-0 sidebar-user">
                @auth
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-fin-orange/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 sidebar-user-text">
                        <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 sidebar-logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="logout-text">Logout</span>
                    </button>
                </form>
                <button id="themeToggle" type="button" aria-label="Switch to light mode"
                    class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-300 dark:border-white/20 bg-slate-100 dark:bg-white/10 hover:bg-slate-200 dark:hover:bg-white/20 transition-all duration-200 text-slate-700 dark:text-white sidebar-theme-btn">
                    <svg id="themeIconMoon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg id="themeIconSun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 12m0 0a4 4 0 100-8 4 4 0 000 8z"></path>
                    </svg>
                    <span id="themeLabel" class="theme-label">Light Mode</span>
                </button>
                <p class="mt-2 text-xs text-center text-slate-400 dark:text-slate-500">v1.0.0</p>
                </form>
                @endauth
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8 h-screen overflow-y-auto transition-all duration-300">
            @if(session('success'))
                <div class="card p-4 mb-6 border-l-4 border-report-green text-report-green dark:text-report-green">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="card p-4 mb-6 border-l-4 border-report-red text-report-red">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="card p-4 mb-6 border-l-4 border-report-red text-report-red">
                    <h3 class="font-bold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @include('components.delete-modal-scripts')
    @include('components.delete-modal')

    <script>
        // Sidebar Toggle Functionality
        (function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            const body = document.body;
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                    body.classList.toggle('main-collapsed');
                    
                    // Toggle icon rotation is handled by CSS
                    // Store state in sessionStorage
                    const isCollapsed = body.classList.contains('sidebar-collapsed');
                    sessionStorage.setItem('sidebarCollapsed', isCollapsed);
                });
            }
            
            // Restore sidebar state from sessionStorage
            const storedState = sessionStorage.getItem('sidebarCollapsed');
            if (storedState === 'true') {
                body.classList.add('sidebar-collapsed');
                body.classList.add('main-collapsed');
            }
        })();

        // Theme Toggle Functionality
        (function() {
            const toggleBtn = document.getElementById('themeToggle');
            const themeIconMoon = document.getElementById('themeIconMoon');
            const themeIconSun = document.getElementById('themeIconSun');
            const themeLabel = document.getElementById('themeLabel');
            const html = document.documentElement;
            
            function updateToggleUI() {
                const isDark = html.classList.contains('dark');
                if (isDark) {
                    themeIconMoon.classList.add('hidden');
                    themeIconSun.classList.remove('hidden');
                    themeLabel.textContent = 'Light Mode';
                    toggleBtn.setAttribute('aria-label', 'Switch to light mode');
                } else {
                    themeIconMoon.classList.remove('hidden');
                    themeIconSun.classList.add('hidden');
                    themeLabel.textContent = 'Dark Mode';
                    toggleBtn.setAttribute('aria-label', 'Switch to dark mode');
                }
            }
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const isDark = html.classList.contains('dark');
                    if (isDark) {
                        html.classList.remove('dark');
                        html.classList.add('light');
                        localStorage.setItem('theme', 'light');
                    } else {
                        html.classList.remove('light');
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    updateToggleUI();
                });
            }
            
            // Initialize toggle UI on load
            updateToggleUI();
        })();
    </script>
</body>
</html>
