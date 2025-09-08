<x-filament-widgets::widget>
    <x-filament::section >
        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-6">
                {{-- IF more than one employee selected, we cannot show these messages --}}
                    <div class="text-sm space-y-2 my-4 w-[60%]">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Trenutno dostupno:</span>
                            <span class="font-semibold">{!! $this->currentlyAvailableAllowanceDays !!} dana</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Rezervirate radnih dana:</span>
                            <span class="font-semibold">{!! $this->bookingDays !!} dana</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 text-left">{{ $this->dateRange }}</div>
                        <div class="flex justify-between pt-2">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Dostupno nakon rezervacije</span>
                            <span class="font-semibold">{!! $this->daysAvailableAfterReservation !!} dana</span>
                        </div>
                        <small class="text-gray-600 italic ">
                            Praznici i neradni dani ne ulaze u broj dana koje rezervirate.
                        </small>
                    </div>

                <x-filament::button type="submit">
                    Rezerviraj {{ $this->bookingDays }} dana
                </x-filament::button>
            </div>
        </form>

        <x-filament-actions::modals/>
    </x-filament::section>
</x-filament-widgets::widget>
