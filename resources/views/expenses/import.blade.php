@extends('layouts.main')

@section('content')
<div class="mb-8">
    <a href="{{ route('expenses.index') }}" class="text-blue-400 hover:text-blue-300 mb-2 inline-block">← Back to Expenses</a>
    <h1 class="text-3xl font-bold text-off-black">Import Expenses</h1>
    <p class="text-black-50">Import expenses from text or file</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-off-black mb-4">Option 1: Copy & Paste</h2>
        
        <form action="{{ route('expenses.import.preview') }}" method="POST">
            @csrf
            <input type="hidden" name="paste_mode" value="1">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Boat</label>
                        <select name="boat_id" required 
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                            <option value="">Select Boat</option>
                            @foreach($boats as $boat)
                                <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-black-50 mb-1">Expense Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing Date (Optional)</label>
                    <select name="landing_id" 
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                        <option value="">Select Landing</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}">{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-black-50 mb-1">Paste Data (CSV Format)</label>
                    <textarea name="paste_data" rows="8" placeholder="2026-03-14|Other|iwita and jose|40000.00" 
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none font-mono text-sm"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Format: Date|Type|Vendor|Amount (one per line)</p>
                    <p class="text-xs text-gray-500 mt-1">Example:</p>
                    <pre class="text-xs text-black-50 bg-gray-800 p-2 rounded mt-1">2026-03-14|Other|iwita and jose|40000.00
2026-03-14|BT Tea|iwita|5000.00
2026-03-14|Bt Salary|ashiqa|8500.00
2026-03-14|Diesel|bejoy|591000.00</pre>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>

    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-semibold text-off-black mb-4">Option 2: Upload File</h2>
        
        <form action="{{ route('expenses.import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="paste_mode" value="0">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-black-50 mb-1">Boat</label>
                        <select name="boat_id" required 
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                            <option value="">Select Boat</option>
                            @foreach($boats as $boat)
                                <option value="{{ $boat->id }}">{{ $boat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-black-50 mb-1">Expense Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-black-50 mb-1">Landing Date (Optional)</label>
                    <select name="landing_id" 
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                        <option value="">Select Landing</option>
                        @foreach($landings as $landing)
                            <option value="{{ $landing->id }}">{{ $landing->date->format('Y-m-d') }} - {{ $landing->boat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-black-50 mb-1">Upload File (.txt/.csv)</label>
                    <input type="file" name="csv_file" accept=".txt,.csv"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Format: Date|Type|Vendor|Amount (CSV)</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
                    Preview Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection