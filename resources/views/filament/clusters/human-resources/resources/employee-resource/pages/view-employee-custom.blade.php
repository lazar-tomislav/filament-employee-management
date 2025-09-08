<x-filament-panels::page>
    <div x-data="{ activeTab: @entangle('activeTab') }" class="pb-32">
        {{-- Custom Header --}}
        <header class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                {{-- Left Side: User Info --}}
                <div class="flex items-center space-x-4">
                    {{-- Avatar --}}
                    <div class="relative">
                        <img class="w-20 h-20 rounded-full bg-cover bg-center"
                             src="{{ $record->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name . ' ' . $record->last_name) . '&color=7F9CF5&background=EBF4FF' }}"
                             alt="{{ $record->first_name }} {{ $record->last_name }}">
                        <span
                            class="absolute bottom-0 right-0 block h-5 w-5 rounded-full bg-green-400 border-2 border-white dark:border-gray-800"></span>
                    </div>

                    {{-- Name, Title, Company --}}
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $record->first_name }} {{ $record->last_name }}
                        </h1>

                        <p class="text-md text-gray-500 dark:text-gray-400">
                            @if($record->title || $record->company)
                                <span>{{ $record->title }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $record->company }}</span>
                            @endif
                            <span class="flex space-y-2 pt-1">
                                <x-filament::icon class="pt-1"
                                                  :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelope"/>
                                <a class="hover:text-primary-500 hover:underline"
                                   href="mailto:{{ $record->email }}">{{ $record->email }}</a>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource::getUrl('edit',['record'=>$record->id])}}"
                       class="p-2 rounded-full hover:text-gray-500 hover:bg-gray-100 ">
                        <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedPencil"></x-filament::icon>
                    </a>
                </div>

            </div>


            {{-- Navigation Tabs --}}
            <nav class="mt-6 -mb-6 -mx-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-8 px-6">
                    <button @click.prevent="activeTab = 'info'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'info',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'info'
                            }"
                            class="py-4 px-1 border-b-2 text-sm font-medium">
                        Osnovne informacije
                    </button>
                    <button @click.prevent="activeTab = 'time'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'time',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'time'
                            }"
                            class="py-4 px-1 border-b-2 text-sm font-medium">
                        Evidencija rada
                    </button>
                    <button @click.prevent="activeTab = 'absence'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'absence',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'absence'
                            }"
                            class="py-4 px-1 border-b-2 text-sm font-medium">
                        Odsutnost
                    </button>
                    <button @click.prevent="activeTab = 'monthly_report'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'monthly_report',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== 'monthly_report'
                            }"
                            class="py-4 px-1 border-b-2 text-sm font-medium">
                        Mjesečni izvještaj
                    </button>
                </div>
            </nav>
        </header>


        {{-- Tab Content --}}
        <div class="mt-6">
            <div x-show="activeTab === 'info'" x-cloak>
                <div class="flex justify-center">
                    <div class="w-full space-y-4" wire:ignore>
                        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\EmployeeDocumentsWidget::class, ["record" => $record])
                        {{ $this->employeeInfoList }}
                        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\LeaveAllowanceWidget::class)
                    </div>
                </div>
            </div>
            <div x-show="activeTab === 'time'" x-cloak class="flex justify-center">
                <div class="w-full">
                    @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\InsertTimeLogWidget::class, ["record" => $record])
                </div>
            </div>
            <div x-show="activeTab === 'absence'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="col-span-1 md:col-span-5">
                        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\RequestLeaveWidget::class,["record"=>$record])
                    </div>

                    <div class="col-span-1 md:col-span-7 flex flex-col gap-4">
                        <div class="pb-2 text-sm text-gray-950 font-medium">
                            Trenutne i nadolazeće odsutnosti
                        </div>
                        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\AbsenceWidget::class,['absenceType' => 'current', "record" => $record])

                        <div class="pb-2 text-sm text-gray-950 font-medium">
                            Prošle odsutnosti
                        </div>
                        @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\AbsenceWidget::class,['absenceType' => 'past',"record" => $record])
                    </div>
                </div>
            </div>
            <div x-show="activeTab === 'monthly_report'" x-cloak>
                @livewire(\Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets\MonthlySummaryWidget::class, ["record" => $record])
            </div>
        </div>
    </div>
</x-filament-panels::page>
