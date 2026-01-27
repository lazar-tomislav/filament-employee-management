@component('mail::message')
# Novi zahtjev za odsustvo - potrebno vaše odobrenje

Zaposlenik **{{ $leaveRequest->employee->full_name }}** je podnio zahtjev za odsustvo.

**Detalji zahtjeva:**
- **Tip:** {{ $leaveRequest->type->getLabel() }}
- **Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}
- **Broj dana:** {{ $leaveRequest->days_count }}

@if($leaveRequest->notes)
**Napomena zaposlenika:** {{ $leaveRequest->notes }}
@endif

Kao voditelj odjela, molimo vas da pregledate i odobrite ovaj zahtjev.

@component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource::getUrl()])
Pregledaj zahtjev
@endcomponent

Hvala,<br>
{{ config('app.name') }}
@endcomponent
