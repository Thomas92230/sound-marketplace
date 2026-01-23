<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard artiste') }}
            </h2>
            <a href="/upload" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                + Ajouter un titre
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Ventes (completed)</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $salesCount }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Chiffre d'affaires (brut)</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($grossRevenueCents / 100, 2) }} €</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Payouts (en attente)</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ number_format($pendingPayoutsCents / 100, 2) }} €</div>
                    <div class="text-xs text-gray-500 mt-2">Déjà payés: {{ number_format($paidPayoutsCents / 100, 2) }} €</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Dernières ventes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">Morceau</th>
                                    <th class="py-2 pr-4">Acheteur</th>
                                    <th class="py-2 pr-4">Montant</th>
                                    <th class="py-2 pr-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($completedSales as $sale)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">
                                            @if($sale->track)
                                                <a class="text-blue-600 hover:text-blue-800" href="{{ route('tracks.show', $sale->track) }}">
                                                    {{ $sale->track->title }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">
                                            {{ $sale->user?->name ?? '—' }}
                                            <div class="text-xs text-gray-500">{{ $sale->user?->email }}</div>
                                        </td>
                                        <td class="py-2 pr-4">{{ $sale->formatted_amount }}</td>
                                        <td class="py-2 pr-4">{{ $sale->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-center text-gray-500" colspan="4">Aucune vente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $completedSales->links() }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Mes payouts</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">Montant</th>
                                    <th class="py-2 pr-4">Statut</th>
                                    <th class="py-2 pr-4">Achat</th>
                                    <th class="py-2 pr-4">Payé le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payouts as $payout)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">{{ $payout->formatted_amount }}</td>
                                        <td class="py-2 pr-4">
                                            <span class="px-2 py-1 rounded text-xs bg-gray-100">
                                                {{ $payout->status }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-4">
                                            @if($payout->purchase)
                                                #{{ $payout->purchase->id }}
                                                @if($payout->purchase->track)
                                                    <span class="text-xs text-gray-500">({{ $payout->purchase->track->title }})</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $payout->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-center text-gray-500" colspan="4">Aucun payout.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $payouts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

