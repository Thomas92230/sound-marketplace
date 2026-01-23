<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Morceaux') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher un titre / artiste..."
                            class="w-full sm:max-w-md border-gray-300 rounded-md"
                        />
                        <button class="bg-gray-900 text-white px-4 py-2 rounded-md">
                            Filtrer
                        </button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">Titre</th>
                                    <th class="py-2 pr-4">Artiste</th>
                                    <th class="py-2 pr-4">Prix</th>
                                    <th class="py-2 pr-4">Créé</th>
                                    <th class="py-2 pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tracks as $track)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('tracks.show', $track) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $track->title }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4">
                                            {{ $track->artist_name }}
                                            @if($track->artist)
                                                <div class="text-xs text-gray-500">{{ $track->artist->email }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ number_format($track->price_cents / 100, 2) }} €</td>
                                        <td class="py-2 pr-4">{{ $track->created_at?->format('Y-m-d') }}</td>
                                        <td class="py-2 pr-4">
                                            <form method="POST" action="{{ route('admin.tracks.delete', $track) }}" onsubmit="return confirm('Supprimer ce morceau ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-800">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-center text-gray-500" colspan="5">
                                            Aucun morceau.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $tracks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

