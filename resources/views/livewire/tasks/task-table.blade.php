<div class="mb-8  {{$this->status->getColorClass()}}"  x-data="{ collapsed: $wire.entangle('isCollapsed') }"

     >
    <div class="mb-4 flex  justify-between">
        <h3 class="text-lg font-semibold ml-1 text-gray-900 dark:text-white flex items-center space-x-1 cursor-pointer group"
            wire:click="toggleCollapse">
            <x-filament::icon
                :icon="$isCollapsed ? \Filament\Support\Icons\Heroicon::ChevronRight : \Filament\Support\Icons\Heroicon::ChevronDown"
                class="transition-transform duration-200"
            />
            <span>{{ $status->getLabel() }}</span>

            <x-filament::badge
                :color="$status->getColor()"
                class="rounded-[100%] opacity-0 group-hover:opacity-100 transition-opacity duration-200"
            >{{count($this->table->getRecords())}} Tasks</x-filament::badge>
        </h3>
        <x-filament::icon
            wire:click="mountAction('quickCreateAction')"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedPlus"
            :size="\Filament\Support\Enums\IconSize::Large"
            color="primary"
            class="cursor-pointer"
            x-show="!collapsed"
            x-collapse.duration.100ms
        />
    </div>

    <div wire:key="table-{{ $status->value }}"
         x-show="!collapsed"
         x-collapse.duration.100ms>
        {{ $this->table }}
    </div>

    @livewire('filament-employee-management::tasks.edit-task-modal')

    <x-filament-actions::modals/>
</div>
