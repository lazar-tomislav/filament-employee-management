@php
    use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
@endphp

<x-filament-panels::page>
    <div class="space-y-6" wire:ignore>

        @if(isset($statusProjekta))
            @livewire('filament-employee-management::projects.project-table', ['status' => $statusProjekta], key('project-table-'.$statusProjekta->value))
        @else
            @foreach([
                StatusProjekta::Priprema,
                StatusProjekta::Provedba,
                StatusProjekta::Finalizacija
            ] as $status)
                @livewire('filament-employee-management::projects.project-table', ['status' => $status], key('project-table-'.$status->value))
            @endforeach
        @endif
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
