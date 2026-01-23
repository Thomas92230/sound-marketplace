<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Achats') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
                        <select name="status" class="border-gray-300 rounded-md sm:max-w-xs">
                            <option value="">Tous les statuts</option>
                            @foreach(['pending' => 'pending', 'completed' => 'completed', 'failed' => 'failed'] as $k => $label)
                                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="bg-gray-900 text-white px-4 py-2 rounded-md">
                            Filtrer
                        </button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">Utilisateur</th>
                                    <th class="py-2 pr-4">Morceau</th>
                                    <th class="py-2 pr-4">Montant</th>
                                    <th class="py-2 pr-4">Statut</th>
                                    <th class="py-2 pr-4">Créé</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">
                                            {{ $purchase->user?->name ?? '—' }}
                                            <div class="text-xs text-gray-500">{{ $purchase->user?->email }}</div>
                                        </td>
                                        <td class="py-2 pr-4">
                                            @if($purchase->track)
                                                <a class="text-blue-600 hover:text-blue-800" href="{{ route('tracks.show', $purchase->track) }}">
                                                    {{ $purchase->track->title }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $purchase->formatted_amount }}</td>
                                        <td class="py-2 pr-4">
                                            <span class="px-2 py-1 rounded text-xs bg-gray-100">
                                                {{ $purchase->status }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-4">{{ $purchase->created_at?->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-center text-gray-500" colspan="5">
                                            Aucun achat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $purchases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

