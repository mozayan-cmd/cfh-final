<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-off-black" id="deleteModalTitle">Delete</h3>
            <button onclick="closeDeleteModal()" class="text-black-50 hover:text-off-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="deleteWarning" class="hidden bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4 mb-4">
            <p class="text-yellow-400 font-medium mb-2">Warning: This record has related data!</p>
            <p class="text-black-50 text-sm mb-3">Deleting this will leave the following records orphaned:</p>
            <div id="relatedList" class="space-y-2"></div>
            <p class="text-yellow-400 text-sm mt-3 font-medium">Are you sure you want to delete?</p>
        </div>
        
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
            <p class="text-red-400 font-medium">Are you sure you want to delete this?</p>
            <p class="text-black-50 text-sm mt-1">This action cannot be undone.</p>
        </div>

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