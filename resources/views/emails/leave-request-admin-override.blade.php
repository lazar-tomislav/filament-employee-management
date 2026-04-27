@component('mail::message')
# Promjena statusa zahtjeva za godišnji odmor

Vaš odobreni zahtjev za godišnji odmor ({{ $leaveRequest->start_date->format('d.m.Y') }} – {{ $leaveRequest->end_date->format('d.m.Y') }}) administrator je stornirao.

**Razlog:** {{ $reason }}

@if($workHoursApproverName)
Za pitanja se obratite osobi: {{ $workHoursApproverName }} (voditelj za radne sate).
@else
Za pitanja se obratite voditelju za radne sate.
@endif

Hvala,<br>
{{ config('app.name') }}
@endcomponent
