<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Odluka o korištenju godišnjeg odmora</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Neophodno za ispravan prikaz hrvatskih/bosanskih slova (č, ć, ž, š, đ) u PDF-u */
            font-size: 14px;
            margin: 0;
            padding: 30px 40px;
            color: #000;
            line-height: 1.5;
        }

        /* Zaglavlje (Header) */
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border: none;
        }
        .header-table td {
            border: none;
            vertical-align: top;
        }
        .logo-cell {
            width: 50%;
            text-align: left;
        }
        .logo {
            max-height: 70px;
            width: auto;
            margin-bottom: 10px;
        }
        .company-info {
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        /* Datumi i mjesto */
        .date-section {
            margin-top: 20px;
            margin-bottom: 30px;
            font-size: 14px;
        }

        /* Uvodni paragraf */
        .intro-text {
            text-align: justify;
            margin-bottom: 40px;
        }

        /* Naslov */
        .main-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
            line-height: 1.3;
        }

        /* Numerirana lista (Tijelo odluke) */
        .decision-body {
            margin-bottom: 30px;
        }
        .decision-item {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .decision-number {
            display: table-cell;
            width: 30px;
            font-weight: bold;
            vertical-align: top;
        }
        .decision-text {
            display: table-cell;
            text-align: justify;
            vertical-align: top;
        }

        /* Obrazloženje */
        .explanation-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .explanation-text {
            text-align: justify;
            margin-bottom: 50px;
        }

        /* Podnožje i Potpisi (Footer) */
        .footer-table {
            width: 100%;
            border: none;
            margin-top: 40px;
            font-size: 11px;
        }
        .footer-table td {
            border: none;
            vertical-align: bottom;
        }
        .company-details-cell {
            width: 60%;
            line-height: 1.4;
            color: #555;
        }
        .signature-cell {
            width: 40%;
            text-align: center;
        }
        .director-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
            color: #000;
        }
        .director-title {
            font-size: 12px;
            margin-bottom: 20px;
            color: #000;
        }
        .signature-area {
            position: relative;
            height: 100px;
            width: 100%;
            border-bottom: 1px solid #ccc; /* Linija za potpis, ukloniti po želji */
        }

        /* Predikcija slika potpisa i pečata */
        .signature-img {
            max-height: 80px;
            max-width: 150px;
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
        }
        .stamp-img {
            max-height: 90px;
            max-width: 90px;
            position: absolute;
            bottom: 5px;
            left: 10%;
            opacity: 0.8;
            z-index: 1;
        }
    </style>
</head>
<body>

<!-- Zaglavlje s logom i osnovnim podacima -->
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <!-- Logotip tvrke -->
            @if(isset($logoPath) && $logoPath)
                <img src="{{ $logoPath }}" class="logo" alt="Logo">
            @else
                <!-- Fallback tekst ukoliko nema loga -->
                <h2 style="margin:0; color:#555;">Razvojna agencija Split - RaST d.o.o.</h2>
            @endif

            <div class="company-info">
                <strong>Razvojna agencija Split - RaST d.o.o.</strong><br>
                HR-21000 Split, Dračevac 3D<br>
                Kontakt: Inforast@agencija-rast.hr
            </div>
        </td>
    </tr>
</table>

<!-- Mjesto i datum -->
<div class="date-section">
    Split, {{ $decisionDate }} godine
</div>

<!-- Uvodni zakonodavni okvir -->
<div class="intro-text">
    Na temelju odredbe članka 76. i 79. te članka 85. stavka 4. Zakona o radu (NN br. 93/14, 127/17, 98/19, 151/22, 46/23, 64/23), te u skladu s odredbama Pravilnika o radu sa sistematizacijom i organizacijom radnih mjesta, poslodavac <strong>Razvojna agencija Split - RaST d.o.o.</strong>, sa sjedištem na adresi Dračevac 3D, 21000 Split, zastupano po direktoru Goranu Batiniću, donosi sljedeću
</div>

<!-- Naslov -->
<div class="main-title">
    ODLUKU<br>
    O KORIŠTENJU GODIŠNJEG ODMORA
</div>

<!-- Točke odluke s varijablama -->
<div class="decision-body">
    <div class="decision-item">
        <div class="decision-number">1.</div>
        <div class="decision-text">
            Radnica/nik <strong>{{ $employeeName }}</strong>, koja/i obavlja poslove <strong>{{ $jobTitle }}</strong> kod Poslodavca, ima pravo na korištenje plaćenog godišnjeg odmora za kalendarsku <strong>{{ $year }}</strong> godinu u trajanju od ukupno <strong>{{ $totalLeaveDays }}</strong> radnih dana.
        </div>
    </div>

    <div class="decision-item">
        <div class="decision-number">2.</div>
        <div class="decision-text">
            Imenovani radnik će koristiti godišnji odmor u trajanju od <strong>{{ $requestedDays }}</strong> dana, datum <strong>{{ $startDate }}</strong> godine i vratiti se na posao <strong>{{ $returnDate }}</strong> godine.
        </div>
    </div>

    <div class="decision-item">
        <div class="decision-number">3.</div>
        <div class="decision-text">
            Imenovani je time iskoristio <strong>{{ $totalUsedDays }}</strong> dan/a godišnjeg odmora za {{ $year }} godinu. Radniku ostaje još <strong>{{ $remainingDays }}</strong> dana godišnjeg odmora.
        </div>
    </div>

    <div class="decision-item">
        <div class="decision-number">4.</div>
        <div class="decision-text">
            Radnik za vrijeme trajanja godišnjeg odmora ima pravo na naknadu u visini njegove prosječne mjesečne plaće isplaćene u prethodna tri mjeseca.
        </div>
    </div>
</div>

<!-- Obrazloženje -->
<div class="explanation-title">Obrazloženje</div>
<div class="explanation-text">
    Ukupno trajanje godišnjeg odmora za Radnika utvrđeno je u skladu s odredbama Zakona o radu (NN br. 93/14, 127/17, 98/19, 151/22, 46/23, 64/23), te na temelju odredaba Ugovora o radu na neodređeno vrijeme između poslodavca Razvojne agencije Split - RaST d.o.o. i radnice/nika {{ $employeeName }} i primjenjivih odredbi Pravilnika o radu sa sistematizacijom i organizacijom radnih mjesta. Na temelju gore navedenog, donosi se odluka kao u izreci.
</div>

<!-- Podnožje i potpisi -->
<table class="footer-table">
    <tr>
        <!-- Informacije o tvrtki -->
        <td class="company-details-cell">
            <strong>Razvojna agencija Split - RaST d.o.o.</strong><br>
            Trgovački sud u Splitu - MBS - 060218245<br>
            Temeljni kapital 2.654,46 €, uplaćen u cijelosti<br>
            Uprava: Goran Batinić, član uprave<br><br>
            Žiro račun u OTP banci d.d., Split<br>
            IBAN - HR5024070001100631769<br>
            SWIFT(BIC) - OTPVHR2X
        </td>

        <!-- Potpis i pečat -->
        <td class="signature-cell">
            <div class="director-name">Goran Batinić</div>
            <div class="director-title">direktor Razvojne agencije Split - RaST d.o.o.</div>

            <div class="signature-area">
                <!-- Dinamički prikaz pečata -->
                @if(isset($stampPath) && $stampPath)
                    <img src="{{ $stampPath }}" class="stamp-img" alt="Pečat poslodavca">
                @endif

                <!-- Dinamički prikaz potpisa -->
                @if(isset($directorSignaturePath) && $directorSignaturePath)
                    <img src="{{ $directorSignaturePath }}" class="signature-img" alt="Potpis direktora">
                @endif
            </div>
        </td>
    </tr>
</table>

</body>
</html>
