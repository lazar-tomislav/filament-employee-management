<div>
    <div class="bg-white dark:bg-gray-800 flex items-center flex-col md:flex-row w-full mb-4 shadow rounded-md  ">
        <!-- Datepicker za odabir tjedna -->
        <div class="dark:bg-gray-800 p-4 rounded-lg w-full md:w-36 ">
            {{ $this->weekForm }}
        </div>

        <div class="border-l border-gray-200 dark:border-gray-700 h-12 mx-2 pr-4"></div>

        <!-- Početak komponente: Tjedni pregled vremena -->
        <div class="w-full rounded-lg flex items-center justify-between overflow-x-auto">

            <!-- Dani u tjednu -->
            <div class="flex items-center gap-2 flex-1 ">
                <!-- Radni dani -->
                <div class="flex items-center gap-1 flex-1">
                    @foreach($this->getWeekDays() as $day)
                         <div wire:click="selectDate('{{ $day['date'] }}')"
                              class="flex flex-col items-center text-black dark:text-white justify-center flex-1 py-2 rounded-md cursor-pointer transition-colors
                         @if($day['is_selected']) border-b-4 border-primary-600 dark:border-primary-500
                         @else hover:bg-red-50 dark:hover:bg-gray-800 @endif">
                        <span
                            class="text-sm font-semibold @if($day['is_selected']) text-primary-600 dark:text-primary-400 @else dark:text-gray-100 @endif">
                            {{ $day['hours'] }}
                        </span>
                             <span
                                 class="text-xs @if($day['is_selected']) text-primary-500 dark:text-primary-300 @else text-black dark:text-white @endif">
                             {{ $day['day_name'] }} {{ $day['day_number'] }}
                             </span>
                        </div>
                    @endforeach
                </div>

            </div>

            {{--        Vertical HR--}}
            <div class="border-l border-gray-200 dark:border-gray-700 h-12 mx-2"></div>

            <!-- Ukupno vrijeme kao zadnja stavka -->
            <div class="flex flex-col items-center justify-center md:px-4 md:py-2 md:ml-2">
                <span
                    class="text-base md:text-lg font-semibold text-black dark:text-white">{{ $this->getTotalWeekHours() }}</span>
                <span class="text-xs text-gray-800 dark:text-white">Ukupno</span>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div class="col-span-1 md:col-span-5">
            <div class="bg-white dark:bg-gray-800 shadow rounded-md p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($this->selectedDate)->format('d.m.Y') }}
                    </h3>
                </div>
                <form wire:submit="create">
                    {{ $this->form }}

                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Unesi sate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-span-1 md:col-span-7 flex flex-col gap-4">
            @php
                $timeLogs = $this->getTimeLogsForSelectedDate();
            @endphp

            @if($timeLogs->count() > 0)
                <div class="space-y-4">
                    @foreach($timeLogs as $timeLog)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg flex items-start p-4 shadow gap-4 border-l-3 border-accent-green">
                            <div
                                class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-md {{$timeLog['color']}}">
                                <span class="font-bold text-2xl text-accent-green -translate-y-0.5">/ /</span>
                            </div>
                            <div class="flex-grow">
                                <p class="font-semibold text-md text-gray-900 dark:text-white">{{$timeLog['name']}}</p>
                                <div class="italic prose prose-sm prose-invert text-gray-600 dark:text-gray-300">
                                    {!! $timeLog['description'] !!}
                                </div>
                            </div>
                            <div
                                class="w-auto px-2 h-8 flex-shrink-0 flex items-center justify-center rounded bg-red-400/50">
                                     <span
                                         class="font-bold text-sm text-accent-green dark:text-accent-green-light">{{$timeLog['hours']}} h</span>
                            </div>

                            <!-- Time Log Actions -->
                            @if($timeLog['can_edit'] || $timeLog['can_delete'])
                                <x-filament-actions::group :actions="[
                                            ($this->editAction)(['timeLog'=>$timeLog['id']])->visible($timeLog['can_edit']),
                                            ($this->deleteAction)(['timeLog'=>$timeLog['id']])->visible($timeLog['can_delete']),
                                        ]"
                                                           dropdown-placement="left"
                                />
                            @endif
                        </div>

                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nema unesenih sati</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Za {{ \Carbon\Carbon::parse($this->selectedDate)->format('d.m.Y') }} još nisu uneseni radni
                        sati.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Action Modals -->
    <x-filament-actions::modals/>
</div>
