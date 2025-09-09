@component('mail::message')
# Status vašeg zahtjeva je ažuriran

@if($leaveRequest->status === 'approved')
## Vaš zahtjev za godišnji odmor je odobren.

**Odobreni dani:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}

**Napomena:**
{{ $leaveRequest->notes ?? 'Nema napomene.' }}
@elseif($leaveRequest->status === 'rejected')
## Vaš zahtjev za godišnji odmor je odbijen.

**Period:** {{ $leaveRequest->start_date->format('d.m.Y') }} - {{ $leaveRequest->end_date->format('d.m.Y') }}

**Razlog odbijanja:**
{{ $leaveRequest->rejection_reason ?? 'Nije naveden razlog.' }}
@endif

Hvala,<br>
{{ config('app.name') }}
@endcomponent
