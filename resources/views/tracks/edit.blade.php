<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier le morceau
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('tracks.update', $track) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Titre du morceau
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $track->title) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="artist_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom de l'artiste
                            </label>
                            <input type="text" 
                                   id="artist_name" 
                                   name="artist_name" 
                                   value="{{ old('artist_name', $track->artist_name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            @error('artist_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="price_cents" class="block text-sm font-medium text-gray-700 mb-2">
                                Prix (en centimes)
                            </label>
                            <input type="number" 
                                   id="price_cents" 
                                   name="price_cents" 
                                   value="{{ old('price_cents', $track->price_cents) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            <p class="text-sm text-gray-500 mt-1">
                                Prix actuel : {{ number_format($track->price_cents / 100, 2) }} €
                            </p>
                            @error('price_cents')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">
                                Mettre à jour
                            </button>
                            <a href="{{ route('dashboard') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-medium">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>