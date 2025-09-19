<div wire:init="onModalLoad">
    <x-filament::modal
        id="edit-task-modal"
        sticky-header="true"
        sticky-footer="true"
        :slide-over="true"
        width="4xl"
    >
        <x-slot name="heading">
            {{ $task?->title ?? 'Novi zadatak' }}
        </x-slot>

        <div class="w-full justify-start ">
            <x-filament::tabs>
                <x-filament::tabs.item
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedHome"
                    :active="$activeTab === 'updates'"
                    wire:click="$set('activeTab', 'updates')"
                >
                    Ažuriranja
                </x-filament::tabs.item>


                <x-filament::tabs.item
                    :active="$activeTab === 'taskEdit'"
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedPencil"
                    wire:click="$set('activeTab', 'taskEdit')"
                >
                    Uredi zadatak
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'activity'"
                    wire:click="$set('activeTab', 'activity')"
                >
                    Aktivnost
                </x-filament::tabs.item>
            </x-filament::tabs>

            <div class="mt-4">
                @if($activeTab === 'updates')
                    @if($task)
                        <livewire:filament-employee-management::tasks.task-activity :task="$task"
                                                                                    :key="'task-activity-'.$task->id"/>
                    @endif
                @endif

                @if($activeTab === 'taskEdit')
                    <div wire:key="task-form-{{ $task?->id ?? 'new' }}">
                        {{$this->editTaskForm}}
                    </div>
                @endif
                @if($activeTab === 'activity')

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-600">Molimo odaberite zadatak za prikaz aktivnosti.</p>
                    </div>
                @endif
            </div>
        </div>

        <x-slot name="footer">
            {{$this->saveAction}}
            {{$this->cancelAction}}
        </x-slot>

    </x-filament::modal>

</div>
