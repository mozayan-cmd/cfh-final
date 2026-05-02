@props(['item' => null, 'type' => 'item', 'related' => []])

<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black">Delete {{ ucfirst($type) }}</h3>
            <button onclick="closeDeleteModal()" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        @if(count($related) > 0)
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4 mb-4">
                <p class="text-yellow-400 font-medium mb-2">Warning: This {{ $type }} has related records!</p>
                <p class="text-black-50 text-sm mb-3">Deleting this {{ $type }} will leave the following records orphaned:</p>
                
                <div class="space-y-2">
                    @foreach($related as $rel)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-300">{{ $rel['type'] }}</span>
                            <span class="text-off-black">{{ $rel['count'] }} (₹{{ number_format($rel['amount'], 2) }})</span>
                        </div>
                    @endforeach
                </div>
                
                <p class="text-yellow-400 text-sm mt-3 font-medium">Are you sure you want to delete this {{ $type }}?</p>
            </div>
        @else
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
                <p class="text-red-400 font-medium">Are you sure you want to delete this {{ $type }}?</p>
                <p class="text-black-50 text-sm mt-1">This action cannot be undone.</p>
            </div>
        @endif

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-black-50 hover:text-off-black">Cancel</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-off-black px-4 py-2 rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(formAction, relatedData = null) {
    document.getElementById('deleteForm').action = formAction;
    
    var modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    var modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>