@php
    use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
@endphp

<x-filament-panels::page>

    <div class="mt-8 space-y-6">
        @foreach([
            TaskStatus::TODO,
            TaskStatus::IN_PROGRESS,
            TaskStatus::DONE,
            TaskStatus::POSTPONED
        ] as $status)
            @livewire('filament-employee-management::tasks.task-table', ['status' => $status], key('task-table-'.$status->value))
        @endforeach
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
