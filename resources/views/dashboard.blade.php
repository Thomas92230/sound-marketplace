<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="/upload" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                + Ajouter un titre
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Mes morceaux</h3>
                        @if($tracks->count() > 0)
                            <div class="flex gap-2">
                                <button onclick="toggleSelectAll()" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                                    Tout sélectionner
                                </button>
                                <button onclick="toggleDeleteMode()" id="deleteBtn"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                    Mode suppression
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    @if($tracks->count() > 0)
                        <form id="bulkDeleteForm" action="{{ route('tracks.bulk-delete') }}" method="POST" 
                              onsubmit="return confirmDelete()" class="mb-4" style="display: none;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-medium">
                                🗑️ Supprimer la sélection
                            </button>
                        </form>
                    @endif
                    
                    @forelse($tracks as $track)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-4 track-item">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="track_ids[]" value="{{ $track->id }}" 
                                       form="bulkDeleteForm" class="track-checkbox w-4 h-4" style="display: none;">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $track->title }}</h4>
                                    <p class="text-gray-600 text-sm">{{ $track->artist_name }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <audio controls class="h-8" preload="metadata">
                                    <source src="{{ $track->preview_url }}" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'élément audio.
                                </audio>

                                <span class="font-bold text-gray-900">
                                    {{ number_format($track->price_cents / 100, 2) }} €
                                </span>
                                
                                <div class="flex gap-2 action-buttons">
                                    <a href="{{ route('tracks.edit', $track) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        ✏️ Modifier
                                    </a>
                                    
                                    <form action="{{ route('tracks.destroy', $track) }}" method="POST" 
                                          onsubmit="return confirm('Supprimer ce morceau ?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-10">Vous n'avez pas encore ajouté de morceaux.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <script>
    let deleteMode = false;
    
    function toggleDeleteMode() {
        deleteMode = !deleteMode;
        const checkboxes = document.querySelectorAll('.track-checkbox');
        const bulkForm = document.getElementById('bulkDeleteForm');
        const deleteBtn = document.getElementById('deleteBtn');
        const actionButtons = document.querySelectorAll('.action-buttons');
        
        if (deleteMode) {
            // Activer le mode suppression
            checkboxes.forEach(cb => cb.style.display = 'block');
            bulkForm.style.display = 'block';
            deleteBtn.textContent = 'Annuler';
            deleteBtn.className = 'bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm';
            actionButtons.forEach(ab => ab.style.display = 'none');
        } else {
            // Désactiver le mode suppression
            checkboxes.forEach(cb => {
                cb.style.display = 'none';
                cb.checked = false;
            });
            bulkForm.style.display = 'none';
            deleteBtn.textContent = 'Mode suppression';
            deleteBtn.className = 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm';
            actionButtons.forEach(ab => ab.style.display = 'flex');
        }
    }
    
    function toggleSelectAll() {
        if (!deleteMode) return;
        const checkboxes = document.querySelectorAll('.track-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }
    
    function confirmDelete() {
        const selected = document.querySelectorAll('.track-checkbox:checked');
        if (selected.length === 0) {
            alert('Veuillez sélectionner au moins un morceau.');
            return false;
        }
        return confirm(`Supprimer ${selected.length} morceau(x) sélectionné(s) ?`);
    }
    </script>
</x-app-layout>
