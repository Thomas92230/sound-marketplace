<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Paiement réussi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    @if($purchase->status === 'completed')
                        <div class="mb-6">
                            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement réussi !</h1>
                        <p class="text-lg text-gray-600 mb-6">
                            Vous avez acheté avec succès : <strong>{{ $purchase->track->title }}</strong>
                        </p>
                        <p class="text-gray-500 mb-8">
                            Montant payé : <strong>{{ $purchase->formatted_amount }}</strong>
                        </p>

                        <div class="space-y-4">
                            <a href="{{ route('tracks.download', $purchase->track) }}" 
                               class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium">
                                Télécharger le fichier complet
                            </a>
                            <div>
                                <a href="{{ route('tracks.show', $purchase->track) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    Voir les détails du morceau
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement en attente</h1>
                        <p class="text-lg text-gray-600 mb-6">
                            Votre paiement est en cours de traitement.
                        </p>
                        <p class="text-gray-500 mb-8">
                            Vous recevrez une notification une fois le paiement confirmé.
                        </p>

                        <a href="{{ route('purchases.index') }}" 
                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium">
                            Voir mes achats
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>