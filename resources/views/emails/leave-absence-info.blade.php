@component('mail::message')
# Obavijest o odsustvu zaposlenika

Zaposlenik **{{ $leaveRequest->employee->full_name }}** ima odobreno odsustvo.

**Detalji zahtjeva:**
- **Tip:** {{ $leaveRequest->type->getLabel() }}
- **Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}
- **Broj dana:** {{ $leaveRequest->days_count }}

@if($leaveRequest->note)
**Napomena:** {{ $leaveRequest->note }}
@endif

@component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource::getUrl()])
Pregledaj zahtjev
@endcomponent

Hvala,<br>
{{ config('app.name') }}
@endcomponent
