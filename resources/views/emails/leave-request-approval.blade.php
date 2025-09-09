@component('mail::message')
    # Novi zahtjev za dopustom

    Podnesen je novi zahtjev za dopust koji čeka vaše odobrenje.

    @component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\LeaveRequestResource::getUrl()])
        Pregledaj sve zahtjeve
    @endcomponent

    Hvala,<br>
    {{ config('app.name') }}
@endcomponent
