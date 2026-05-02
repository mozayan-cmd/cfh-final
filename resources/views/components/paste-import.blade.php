<div class="card rounded-xl p-6">
    <h2 class="text-xl font-semibold text-off-black mb-4">Option 1: Copy & Paste</h2>
    
    <form action="{{ $action }}" method="POST">
        @csrf
        <input type="hidden" name="paste_mode" value="1">
        
        {{ $slot }}
        
        <div>
            <label class="block text-sm text-black-50 mb-1">Paste Data</label>
            <textarea name="paste_data" rows="8" placeholder="{{ $placeholder }}" 
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-off-black focus:border-blue-500 focus:outline-none font-mono text-sm"></textarea>
            <p class="text-xs text-gray-500 mt-1">{{ $formatHint }}</p>
            <p class="text-xs text-gray-500 mt-1">Example:</p>
            <pre class="text-xs text-black-50 bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ $example }}</pre>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ $cancelUrl }}" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-off-black px-4 py-2 rounded-lg">
                Preview Import
            </button>
        </div>
    </form>
</div>