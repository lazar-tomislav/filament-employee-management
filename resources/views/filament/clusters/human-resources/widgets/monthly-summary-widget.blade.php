<x-filament-widgets::widget>

    <div class="flex w-full justify-center">
        <div class="w-full md:w-8/12 space-y-4">

            <div class="flex space-x-5 justify-between">
                {{ $this->downloadMonthlyTimeReportAction }}

                <x-filament::button
                    wire:click="goToCurrentMonth"
                    size="sm"
                    :color="$this->isCurrentMonth() ? 'gray' : 'primary'"
                    :outlined="!$this->isCurrentMonth()"
                    :disabled="$this->isCurrentMonth()"
                >
                    Danas
                </x-filament::button>

            </div>


            {{-- Month Navigation --}}
            <div class="relative flex items-center justify-center gap-2 py-2">
                <x-filament::button
                    wire:click="previousMonth"
                    icon="heroicon-o-chevron-left"
                    color="gray"
                    size="sm"
                    :disabled="!$this->canGoPrevious()"
                />

                <span class="text-lg font-semibold text-gray-900 dark:text-white min-w-[180px] text-center">
                    {{ $this->getCurrentMonthLabel() }}
                </span>

                <x-filament::button
                    wire:click="nextMonth"
                    icon="heroicon-o-chevron-right"
                    color="gray"
                    size="sm"
                    :disabled="!$this->canGoNext()"
                />


            </div>

            {{ $this->summaryInfoList }}

            @if ($this->workReport)
                @if ($this->workReport->approved_at)
                    {{-- Approved State --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-green-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-green-500/20 text-green-500">
                            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedCheckCircle"
                                              class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Odobreno</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Izvještaj je odobren datuma {{ $this->workReport->approved_at->format('d.m.Y H:i') }}.
                            </div>
                        </div>
                    </div>
                @elseif ($this->workReport->denied_at)
                    {{-- Denied State --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-red-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-red-500/20 text-red-500">
                            <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Odbijeno</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p>Izvještaj je odbijen datuma {{ $this->workReport->denied_at->format('d.m.Y H:i') }}
                                    .</p>
                                <p><span class="font-semibold">Razlog:</span> {{ $this->workReport->deny_reason }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Pending State --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-yellow-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-yellow-500/20 text-yellow-500">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Na čekanju</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Izvještaj čeka na odobrenje.
                            </div>
                        </div>
                    </div>
                @endif
            @else
                {{-- No Report State --}}
                <div
                    class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-gray-300 dark:border-gray-600">
                    <div
                        class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-gray-500/20 text-gray-500">
                        <x-filament::icon icon="heroicon-o-document" class="h-6 w-6"/>
                    </div>
                    <div class="flex-grow">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Za odabrani mjesec još nije prihvaćen ili odbijen izvještaj.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>


    <x-filament-actions::modals/>
</x-filament-widgets::widget>
