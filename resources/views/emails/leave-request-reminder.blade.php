@component('mail::message')
# Podsjetnik: Zahtjev za odsustvo čeka odobrenje

Zaposlenik **{{ $leaveRequest->employee->full_name }}** vas podsjeća da zahtjev za odsustvo čeka vaše odobrenje.

**Detalji zahtjeva:**
- **Tip:** {{ $leaveRequest->type->getLabel() }}
- **Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}
- **Broj dana:** {{ $leaveRequest->days_count }}
- **Podnesen:** {{ $leaveRequest->created_at->format('d.m.Y') }}

@if($leaveRequest->notes)
**Napomena zaposlenika:** {{ $leaveRequest->notes }}
@endif

@component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource::getUrl()])
Pregledaj zahtjev
@endcomponent

Hvala,<br>
{{ config('app.name') }}
@endcomponent
