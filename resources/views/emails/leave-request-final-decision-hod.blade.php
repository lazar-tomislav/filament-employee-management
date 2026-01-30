@component('mail::message')
# Finalna odluka o zahtjevu za odsustvo

Zahtjev za odsustvo zaposlenika **{{ $leaveRequest->employee->full_name }}** koji ste prethodno odobrili je dobio finalnu odluku.

@if($leaveRequest->status->value === 'approved')
**Status: ODOBREN**

Direktor je odobrio zahtjev.
@else
**Status: ODBIJEN**

@if($leaveRequest->rejection_reason)
**Razlog odbijanja:** {{ $leaveRequest->rejection_reason }}
@endif
@endif

**Detalji zahtjeva:**
- **Tip:** {{ $leaveRequest->type->getLabel() }}
- **Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}
- **Broj dana:** {{ $leaveRequest->days_count }}

@if($leaveRequest->directorApprover)
**Odluku donio:** {{ $leaveRequest->directorApprover->full_name }} ({{ $leaveRequest->approved_by_director_at?->format('d.m.Y H:i') }})
@endif

@component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource::getUrl()])
Pregledaj zahtjev
@endcomponent

Hvala,<br>
{{ config('app.name') }}
@endcomponent
