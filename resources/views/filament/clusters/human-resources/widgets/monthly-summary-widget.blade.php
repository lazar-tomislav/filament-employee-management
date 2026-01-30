<x-filament-widgets::widget>

    <div class="flex w-full justify-center">
        <div class="w-full md:w-8/12 space-y-4">

            {{-- End of Month Alert --}}
            @if($this->showEndOfMonthAlert)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="shrink-0">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-yellow-400"/>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                <strong>Upozorenje:</strong> Približava se kraj mjeseca. Molimo potvrdite svoje radne
                                sate.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-2 justify-between">
                <div class="flex flex-wrap gap-2">
                    {{ $this->downloadMonthlyTimeReportAction }}

                    @if($this->canSubmitForReview())
                        {{ $this->submitForReviewAction }}
                    @endif

                    @if($this->canReturnForCorrection())
                        {{ $this->returnForCorrectionAction }}
                    @endif

                    @if($this->canCloseMonth())
                        {{ $this->closeMonthAction }}
                    @endif
                </div>

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
                    {{-- Approved/Locked State --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-green-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-green-500/20 text-green-500">
                            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedLockClosed"
                                              class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Zaključano</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Mjesec je zaključan {{ $this->workReport->approved_at->format('d.m.Y H:i') }}.
                            </div>
                        </div>
                    </div>
                @elseif ($this->workReport->submitted_at)
                    {{-- Submitted/Pending Review State --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-blue-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-blue-500/20 text-blue-500">
                            <x-filament::icon icon="heroicon-o-paper-airplane" class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Poslano na pregled</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Izvještaj je poslan na
                                pregled {{ $this->workReport->submitted_at->format('d.m.Y H:i') }}.
                                @if($approverName = $this->getApproverName())
                                    Čeka odobrenje od: <span class="font-medium">{{ $approverName }}</span>.
                                @else
                                    Čeka odobrenje.
                                @endif
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
                    {{-- Old pending state (report exists but no status set) --}}
                    <div
                        class="bg-white dark:bg-gray-900/50 rounded-lg flex items-start p-4 shadow gap-4 border-l-4 border-yellow-500">
                        <div
                            class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md bg-yellow-500/20 text-yellow-500">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6"/>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-md text-gray-900 dark:text-gray-100">Nema izvještaja</p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Za odabrani mjesec još nije poslan izvještaj na pregled.
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>


    <x-filament-actions::modals/>
</x-filament-widgets::widget>
