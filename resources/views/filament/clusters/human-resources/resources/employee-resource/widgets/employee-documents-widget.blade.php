<x-filament-widgets::widget>

    {{$this->downloadMonthlyTimeReportAction}}
    @if(config('employee-management.telegram-bot-api.is_active'))
        {{$this->connectToTelegramAction}}
    @endif

    <x-filament-actions::modals/>

</x-filament-widgets::widget>
