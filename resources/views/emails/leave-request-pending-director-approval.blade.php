@component('mail::message')
# Zahtjev za odsustvo - potrebno finalno odobrenje

@if($afterHodApproval)
Voditelj odjela je odobrio zahtjev za odsustvo zaposlenika **{{ $leaveRequest->employee->full_name }}**.

Zahtjev sada čeka vaše finalno odobrenje kao direktora.
@else
Zaposlenik **{{ $leaveRequest->employee->full_name }}** je podnio zahtjev za odsustvo koji čeka vaše odobrenje.
@endif

**Detalji zahtjeva:**
- **Tip:** {{ $leaveRequest->type->getLabel() }}
- **Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}
- **Broj dana:** {{ $leaveRequest->days_count }}

@if($leaveRequest->notes)
**Napomena zaposlenika:** {{ $leaveRequest->notes }}
@endif

@if($afterHodApproval && $leaveRequest->headOfDepartmentApprover)
**Odobrio voditelj:** {{ $leaveRequest->headOfDepartmentApprover->full_name }} ({{ $leaveRequest->approved_by_head_of_department_at?->format('d.m.Y H:i') }})
@endif

@component('mail::button', ['url' => \Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource::getUrl()])
Pregledaj zahtjev
@endcomponent

Hvala,<br>
{{ config('app.name') }}
@endcomponent
