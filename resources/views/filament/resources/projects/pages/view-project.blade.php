@php
    use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
@endphp

<x-filament-panels::page>

    <div>
        {{$this->projectInfoList}}
    </div>

    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
        Zadaci projekta
    </h2>

    <div class="space-y-6">
        @foreach([
            TaskStatus::TODO,
            TaskStatus::IN_PROGRESS,
            TaskStatus::DONE,
            TaskStatus::POSTPONED
        ] as $status)
            @livewire('filament-employee-management::tasks.task-table', ['status' => $status, "projectId" => $record->id], key('task-table-'.$status->value))
        @endforeach
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
