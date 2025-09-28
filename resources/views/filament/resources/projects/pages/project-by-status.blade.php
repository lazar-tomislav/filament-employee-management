@php
    use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
@endphp

<x-filament-panels::page>
            @livewire('tasks-by-status', ['status' => $statusProjekta], key('task-table-'.$statusProjekta->value))

    @livewire('edit-entity-modal', ['entityType' => 'task'])
    <x-filament-actions::modals/>
</x-filament-panels::page>
