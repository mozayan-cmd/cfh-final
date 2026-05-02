<script>
function openDeleteModal(formAction, modelType, recordId) {
    fetch(`/api/related-records/${modelType}/${recordId}`)
        .then(res => res.json())
        .then(data => {
            const modal = document.getElementById('deleteConfirmModal');
            const relatedList = document.getElementById('relatedList');
            const warningBox = document.getElementById('deleteWarning');
            const title = document.getElementById('deleteModalTitle');
            
            document.getElementById('deleteForm').action = formAction;
            title.textContent = 'Delete ' + modelType.charAt(0).toUpperCase() + modelType.slice(1);
            
            if (data.length > 0) {
                warningBox.classList.remove('hidden');
                relatedList.innerHTML = data.map(item => 
                    `<div class="flex justify-between text-sm">
                        <span class="text-gray-300">${item.type}</span>
                        <span class="text-white">${item.count} (₹${Number(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})})</span>
                    </div>`
                ).join('');
            } else {
                warningBox.classList.add('hidden');
                relatedList.innerHTML = '';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>