<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mes achats') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse($purchases as $purchase)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $purchase->track->title }}</h3>
                                <p class="text-gray-600 text-sm">{{ $purchase->track->artist_name }}</p>
                                <p class="text-gray-500 text-xs mt-1">
                                    Acheté le {{ $purchase->created_at->format('d/m/Y à H:i') }}
                                    • {{ $purchase->formatted_amount }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                @if($purchase->status === 'completed')
                                    <a href="{{ route('tracks.download', $purchase->track) }}" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Télécharger
                                    </a>
                                @else
                                    <span class="text-yellow-600 text-sm font-medium">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                @endif
                                
                                <a href="{{ route('tracks.show', $purchase->track) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-10">Vous n'avez pas encore fait d'achats.</p>
                        <div class="text-center">
                            <a href="{{ route('home') }}" 
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium">
                                Découvrir la musique
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>