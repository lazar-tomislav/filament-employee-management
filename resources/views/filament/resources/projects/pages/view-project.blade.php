@php
    use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
@endphp

<x-filament-panels::page>

    <div>
        {{$this->projectInfoList}}
    </div>

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
