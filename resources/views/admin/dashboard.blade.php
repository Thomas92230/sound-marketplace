<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panneau d\'administration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Utilisateurs</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['users_count'] }}</p>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir tous →</a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Artistes</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['artists_count'] }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Morceaux</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['tracks_count'] }}</p>
                    <a href="{{ route('admin.tracks.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir tous →</a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Achats</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ $stats['purchases_count'] }}</p>
                    <a href="{{ route('admin.purchases.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir tous →</a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Revenus totaux</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_revenue'] / 100, 2) }} €</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Paiements en attente</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ number_format($stats['pending_payouts'] / 100, 2) }} €</p>
                    <a href="{{ route('admin.payouts.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir tous →</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>