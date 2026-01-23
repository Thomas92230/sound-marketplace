<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil artiste') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-800 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-800 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-6 p-4 border rounded-lg">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="font-semibold">Paiements Stripe (Connect Express)</div>
                                @if($user->stripe_account_id)
                                    <div class="text-sm text-gray-600">
                                        Compte connecté: <span class="font-mono">{{ $user->stripe_account_id }}</span>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-600">Aucun compte Stripe connecté.</div>
                                @endif
                            </div>
                            <a href="{{ route('artist.stripe.start') }}"
                               class="inline-flex items-center justify-center bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-md text-sm font-medium">
                                {{ $user->stripe_account_id ? 'Gérer / terminer la configuration' : 'Connecter Stripe' }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            Une fois connecté, les payouts peuvent être automatisés via Stripe Connect (Transfer).
                        </div>
                    </div>

                    <form method="POST" action="{{ route('artists.update') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Nom -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bio -->
                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700">Biographie</label>
                                <textarea name="bio" 
                                          id="bio" 
                                          rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Avatar -->
                            <div>
                                <label for="avatar" class="block text-sm font-medium text-gray-700">Photo de profil</label>
                                @if($user->avatar)
                                    <div class="mt-2 mb-4">
                                        <img src="{{ Storage::disk('public')->url($user->avatar) }}" 
                                             alt="Avatar actuel" 
                                             class="w-24 h-24 rounded-full object-cover">
                                    </div>
                                @endif
                                <input type="file" 
                                       name="avatar" 
                                       id="avatar" 
                                       accept="image/*"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('avatar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bouton de soumission -->
                            <div class="flex items-center justify-end">
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>