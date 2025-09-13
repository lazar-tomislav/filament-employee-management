<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <div class="flex justify-start">
                    <x-filament-panels::header.simple/>
                </div>
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Dobro došli! Dovršite svoj profil
                    </h2>
                    <p class="text-sm text-gray-600">
                        Za pristup sustavu potrebno je da imate profil zaposlenika. Molimo unesite svoje podatke ili se povežite s postojećim korisnikom.
                    </p>
                </div>
            </div>

            <form wire:submit="create">
                {{ $this->form }}
            </form>
        </div>
    </div>
</x-filament-panels::page>
