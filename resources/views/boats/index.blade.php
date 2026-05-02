@extends('layouts.main')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-off-black">Boats</h1>
        <p class="text-black-50">Manage fishing boats</p>
    </div>
    <button onclick="openModal('createBoatModal')" 
        class="bg-fin-orange hover:bg-fin-orange/90 text-off-black px-4 py-2 rounded-lg">
        + Add Boat
    </button>
</div>

<div class="card rounded-xl overflow-hidden">
    <div class="overflow-x-auto table-container">
    <table class="w-full">
        <thead class="bg-slate-50">
            <tr class="text-slate-600 text-sm font-medium">
                <th class="text-left px-6 py-4">Name</th>
                <th class="text-left px-6 py-4">Owner Phone</th>
                <th class="text-center px-6 py-4">Total Landings</th>
                <th class="text-center px-6 py-4">Latest Landing</th>
                <th class="text-right px-6 py-4">Pending Settlement</th>
                <th class="text-center px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boats as $boat)
            <tr class="border-t border-slate-200 hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $boat->name }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $boat->owner_phone ?? '-' }}</td>
                <td class="px-6 py-4 text-center text-slate-900">{{ $boat->total_landings }}</td>
                <td class="px-6 py-4 text-center text-slate-600">{{ $boat->latest_landing_date ?? '-' }}</td>
                <td class="px-6 py-4 text-right text-yellow-600">₹{{ number_format($boat->pending_settlement, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <button onclick='openEditBoatModal({{ json_encode($boat) }})' class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                    <a href="{{ route('boats.show', $boat) }}" class="text-slate-600 hover:text-slate-800 mr-3">View</a>
                    <button onclick="openDeleteModal('{{ route('boats.destroy', $boat) }}', 'boat', {{ $boat->id }})" class="text-red-600 hover:text-red-800">Delete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-slate-500">No boats found. Add your first boat!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="createBoatModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-900">Add New Boat</h3>
            <button onclick="closeModal('createBoatModal')" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('boats.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Boat Name</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Owner Phone</label>
                    <input type="text" name="owner_phone" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('createBoatModal')" class="px-4 py-2 text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="bg-fin-orange hover:bg-fin-orange/90 text-white px-4 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="editBoatModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-900">Edit Boat</h3>
            <button onclick="closeModal('editBoatModal')" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editBoatForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Boat Name</label>
                    <input type="text" name="name" id="editBoatName" required class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Owner Phone</label>
                    <input type="text" name="owner_phone" id="editBoatPhone" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-fin-orange focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('editBoatModal')" class="px-4 py-2 text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="bg-fin-orange hover:bg-fin-orange/90 text-white px-4 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openEditBoatModal(boat) {
    document.getElementById('editBoatForm').action = '/boats/' + boat.id;
    document.getElementById('editBoatName').value = boat.name;
    document.getElementById('editBoatPhone').value = boat.owner_phone || '';
    document.getElementById('editBoatModal').classList.remove('hidden');
}
</script>
@endsection
