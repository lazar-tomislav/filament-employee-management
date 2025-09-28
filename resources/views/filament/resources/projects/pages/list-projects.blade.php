@php
    use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
@endphp

<x-filament-panels::page>
    <div class="space-y-6" wire:ignore>
        @foreach([
            StatusProjekta::Priprema,
            StatusProjekta::Provedba,
            StatusProjekta::Finalizacija
        ] as $status)
            @livewire('filament-employee-management::projects.project-table', ['status' => $status], key('project-table-'.$status->value))
        @endforeach
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
