<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profil de l'artiste -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start gap-6">
                        @if($artist->avatar)
                            <img src="{{ Storage::disk('public')->url($artist->avatar) }}" 
                                 alt="{{ $artist->name }}" 
                                 class="w-24 h-24 rounded-full object-cover">
                        @else
                            <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-2xl text-gray-600">{{ substr($artist->name, 0, 1) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $artist->name }}</h1>
                            @if($artist->bio)
                                <p class="text-gray-600 mb-4">{{ $artist->bio }}</p>
                            @endif
                            <p class="text-sm text-gray-500">{{ $tracks->count() }} morceau{{ $tracks->count() > 1 ? 'x' : '' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des morceaux -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Discographie</h2>
                    
                    <div class="space-y-4">
                        @forelse($tracks as $track)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $track->title }}</h3>
                                    <p class="text-gray-600 text-sm">{{ $track->artist_name }}</p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <audio controls class="h-8" preload="metadata">
                                        <source src="{{ $track->preview_url }}" type="audio/mpeg">
                                    </audio>

                                    <span class="font-bold text-gray-900">
                                        {{ number_format($track->price_cents / 100, 2) }} €
                                    </span>
                                    
                                    <a href="{{ route('tracks.show', $track) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        Détails
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-10">Aucun morceau disponible pour le moment.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>