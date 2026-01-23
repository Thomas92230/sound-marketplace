<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Paiements artistes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
                        <select name="status" class="border-gray-300 rounded-md sm:max-w-xs">
                            <option value="">Tous les statuts</option>
                            @foreach(['pending' => 'pending', 'paid' => 'paid'] as $k => $label)
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
                                    <th class="py-2 pr-4">Artiste</th>
                                    <th class="py-2 pr-4">Montant</th>
                                    <th class="py-2 pr-4">Statut</th>
                                    <th class="py-2 pr-4">Achat</th>
                                    <th class="py-2 pr-4">Payé le</th>
                                    <th class="py-2 pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payouts as $payout)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">
                                            {{ $payout->user?->name ?? '—' }}
                                            <div class="text-xs text-gray-500">{{ $payout->user?->email }}</div>
                                        </td>
                                        <td class="py-2 pr-4">{{ $payout->formatted_amount }}</td>
                                        <td class="py-2 pr-4">
                                            <span class="px-2 py-1 rounded text-xs bg-gray-100">
                                                {{ $payout->status }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-4">
                                            @if($payout->purchase)
                                                #{{ $payout->purchase->id }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">
                                            {{ $payout->paid_at?->format('Y-m-d H:i') ?? '—' }}
                                        </td>
                                        <td class="py-2 pr-4">
                                            @if($payout->status !== 'paid')
                                                <form method="POST" action="{{ route('admin.payouts.mark-paid', $payout) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-green-700 hover:text-green-900">
                                                        Marquer payé
                                                    </button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-6 text-center text-gray-500" colspan="6">
                                            Aucun payout.
                                        </td>
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

