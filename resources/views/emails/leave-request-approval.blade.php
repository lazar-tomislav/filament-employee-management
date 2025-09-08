@component('mail::message')
    # Novi zahtjev za dopustom

    Podnesen je novi zahtjev za dopust koji čeka vaše odobrenje.

    @component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource::getUrl('edit', ['record' => $leaveRequest->id])])
        Pregledaj zahtjev
    @endcomponent

    Hvala,<br>
    {{ config('app.name') }}
@endcomponent
