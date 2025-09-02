<x-mail::message>
# Dobrodošli

Kreiran vam je korisnički račun na {{ config('app.name') }}. Molimo vas da se prijavite koristeći sljedeće podatke:

**Email:** {{ $email }}
**Lozinka:** {{ $password }}

Preporučujemo da promijenite lozinku nakon prve prijave u postavkama vašeg računa.

Hvala<br>
{{ config('app.name') }}
</x-mail::message>
