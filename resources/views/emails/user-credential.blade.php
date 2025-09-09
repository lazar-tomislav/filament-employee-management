<x-mail::message>
# Dobrodošli

Kreiran vam je korisnički račun na {{ config('app.name') }}.
<br>
<br>
Molimo vas da se prijavite koristeći sljedeće podatke:
<br>
**Email:** {{ $email }}
<br>
**Lozinka:** {{ $password }}

Preporučujemo da promijenite lozinku nakon prve prijave u postavkama vašeg računa.

Hvala<br>
{{ config('app.name') }}
</x-mail::message>
