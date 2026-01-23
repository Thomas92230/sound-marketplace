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
                    <h3 class="text-lg font-semibold mb-4">Mes morceaux</h3>
                    
                    @forelse($tracks as $track)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-4">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $track->title }}</h4>
                                <p class="text-gray-600 text-sm">{{ $track->artist_name }}</p>
                            </div>

                            <div class="flex items-center gap-6">
                                <audio controls class="h-8" preload="metadata">
                                    <source src="{{ $track->preview_url }}" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'élément audio.
                                </audio>

                                <span class="font-bold text-gray-900">
                                    {{ number_format($track->price_cents / 100, 2) }} €
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-10">Vous n'avez pas encore ajouté de morceaux.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
