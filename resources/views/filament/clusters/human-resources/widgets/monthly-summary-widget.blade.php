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

            @php
                $summary = $this->getSummaryData();
            @endphp

            {{-- Hero: Ukupno odrađeno --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-5">
                <div class="flex items-end justify-between mb-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ukupno odrađeno</p>
                        <p class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($summary['total_worked'], 1) }}h
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            od predviđenih <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($summary['totals']['available_hours'], 0) }}h</span>
                        </p>
                        <p class="text-sm font-semibold {{ $summary['percentage'] >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $summary['percentage'] }}%
                        </p>
                    </div>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500 {{ $summary['percentage'] >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}"
                        style="width: {{ min($summary['percentage'], 100) }}%"
                    ></div>
                </div>
            </div>

            {{-- Category cards grid --}}
            @php
                $categories = [
                    ['key' => 'work_hours', 'label' => 'Radni sati', 'icon' => 'heroicon-o-briefcase', 'color' => 'blue'],
                    ['key' => 'work_from_home_hours', 'label' => 'Rad od kuće', 'icon' => 'heroicon-o-home', 'color' => 'indigo'],
                    ['key' => 'overtime_hours', 'label' => 'Prekovremeno', 'icon' => 'heroicon-o-clock', 'color' => 'amber'],
                    ['key' => 'vacation_hours', 'label' => 'Godišnji odmor', 'icon' => 'heroicon-o-sun', 'color' => 'emerald'],
                    ['key' => 'sick_leave_hours', 'label' => 'Bolovanje', 'icon' => 'heroicon-o-heart', 'color' => 'red'],
                    ['key' => 'other_hours', 'label' => 'Plaćeno odsustvo', 'icon' => 'heroicon-o-calendar-days', 'color' => 'purple'],
                    ['key' => 'holiday_hours', 'label' => 'Blagdani', 'icon' => 'heroicon-o-flag', 'color' => 'teal'],
                    ['key' => 'maternity_leave_hours', 'label' => 'Porodiljni dopust', 'icon' => 'heroicon-o-face-smile', 'color' => 'pink'],
                ];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($categories as $cat)
                    @php
                        $value = $summary['totals'][$cat['key']];
                        $isZero = $value == 0;
                        $colorMap = [
                            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-950/30', 'icon' => 'text-blue-500 dark:text-blue-400', 'ring' => 'ring-blue-200 dark:ring-blue-800', 'dot' => 'bg-blue-500'],
                            'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-950/30', 'icon' => 'text-indigo-500 dark:text-indigo-400', 'ring' => 'ring-indigo-200 dark:ring-indigo-800', 'dot' => 'bg-indigo-500'],
                            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-950/30', 'icon' => 'text-amber-500 dark:text-amber-400', 'ring' => 'ring-amber-200 dark:ring-amber-800', 'dot' => 'bg-amber-500'],
                            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/30', 'icon' => 'text-emerald-500 dark:text-emerald-400', 'ring' => 'ring-emerald-200 dark:ring-emerald-800', 'dot' => 'bg-emerald-500'],
                            'red' => ['bg' => 'bg-red-50 dark:bg-red-950/30', 'icon' => 'text-red-500 dark:text-red-400', 'ring' => 'ring-red-200 dark:ring-red-800', 'dot' => 'bg-red-500'],
                            'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-950/30', 'icon' => 'text-purple-500 dark:text-purple-400', 'ring' => 'ring-purple-200 dark:ring-purple-800', 'dot' => 'bg-purple-500'],
                            'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-950/30', 'icon' => 'text-teal-500 dark:text-teal-400', 'ring' => 'ring-teal-200 dark:ring-teal-800', 'dot' => 'bg-teal-500'],
                            'pink' => ['bg' => 'bg-pink-50 dark:bg-pink-950/30', 'icon' => 'text-pink-500 dark:text-pink-400', 'ring' => 'ring-pink-200 dark:ring-pink-800', 'dot' => 'bg-pink-500'],
                        ];
                        $colors = $colorMap[$cat['color']];
                    @endphp
                    <div class="rounded-xl p-4 ring-1 transition-all duration-200
                        {{ $isZero
                            ? 'bg-gray-50 dark:bg-gray-900 ring-gray-200 dark:ring-gray-800 opacity-50'
                            : $colors['bg'] . ' ' . $colors['ring']
                        }}"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <x-filament::icon
                                :icon="$cat['icon']"
                                class="h-5 w-5 {{ $isZero ? 'text-gray-400 dark:text-gray-600' : $colors['icon'] }}"
                            />
                            <span class="text-xs font-medium {{ $isZero ? 'text-gray-400 dark:text-gray-600' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $cat['label'] }}
                            </span>
                        </div>
                        <p class="text-xl font-bold {{ $isZero ? 'text-gray-300 dark:text-gray-700' : 'text-gray-950 dark:text-white' }}">
                            {{ number_format($value, $value == intval($value) ? 0 : 1) }}h
                        </p>
                    </div>
                @endforeach
            </div>

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
