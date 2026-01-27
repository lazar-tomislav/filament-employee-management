<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Zahtjev za godišnji odmor</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* DejaVu supports Croatian characters in PDF */
            font-size: 14px;
            margin: 0;
            padding: 20px 40px;
            color: #000;
        }

        /* Header Layout: Table used for PDF compatibility over Flexbox */
        .header-table { width: 100%; margin-bottom: 40px; border: none; }
        .header-table td { border: none; vertical-align: top; }
        .logo-cell { width: 50%; text-align: left; }
        .logo { max-height: 60px; width: auto; }
        .company-cell {
            width: 50%;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
            color: #555; /* subtle grey as in the reference */
            line-height: 1.2;
        }

        /* Title */
        .main-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
            letter-spacing: 1px;
            color: #8a8a8a; /* TODO done: light gray title */
        }

        /* Main Form Table */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .form-table td {
            border: 1px solid #bdbdbd; /* TODO done: light gray borders */
            padding: 8px 10px;
            vertical-align: middle;
        }

        .col-label {
            width: 35%;
            font-weight: bold;
            background-color: #f9f9f9; /* subtle contrast for labels */
        }
        .col-value { width: 65%; font-weight: normal; }

        /* Specific styling for the DA/NE row */
        .decision-box { font-weight: bold; }
        .decision-box span {
            margin-right: 20px;
            padding: 2px 8px;
            display: inline-block;
        }
        .selected {
            border: 2px solid #000;
            border-radius: 999px; /* pill/circle style */
        }

        /* Footer Text */
        .footer-text {
            font-size: 11px;
            margin-top: 20px;
            margin-bottom: 50px;
            line-height: 1.4;
        }

        /* Signature Section */
        .signature-table { width: 100%; border: none; margin-top: 40px; }
        .signature-table td { border: none; vertical-align: bottom; width: 50%; }
        .signature-label { font-weight: bold; margin-bottom: 40px; display: block; }
        .signature-line { border-bottom: 1px solid #000; width: 80%; display: inline-block; }
        .signature-cell-right { text-align: right; }
        .signature-cell-right .signature-line { text-align: right; }

        /* Signature images in form table */
        .signature-img { max-height: 70px; max-width: 220px; }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="logo">
                @endif
            </td>
            <td class="company-cell">
                {!! $companyName !!}
            </td>
        </tr>
    </table>

    <!-- Title (light gray) -->
    <div class="main-title">ZAHTJEV ZA KORIŠTENJE GODIŠNJEG ODMORA</div>

    <!-- Main Form (light gray borders) -->
    <table class="form-table">
        <tr>
            <td class="col-label">Ime i prezime zaposlenika</td>
            <td class="col-value">{{ $leaveRequest->employee->full_name }}</td>
        </tr>
        <tr>
            <td class="col-label">Odjel</td>
            <td class="col-value">{{ $leaveRequest->employee->department->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="col-label">Dani godišnjeg odmora</td>
            <td class="col-value">{{ $leaveRequest->days_count }}</td>
        </tr>
        <tr>
            <td class="col-label">Ukupni broj radnih dana</td>
            <td class="col-value">{{ $leaveRequest->days_count }}</td>
        </tr>
        <tr>
            <td class="col-label">Datum i dan početka godišnjeg odmora</td>
            <td class="col-value">
                {{ $leaveRequest->start_date->format('d.m.Y') }}
                ({{ $leaveRequest->start_date->locale('hr')->translatedFormat('l') }})
            </td>
        </tr>
        <tr>
            <td class="col-label">Datum i dan završetka godišnjeg odmora</td>
            <td class="col-value">
                {{ $leaveRequest->end_date->format('d.m.Y') }}
                ({{ $leaveRequest->end_date->locale('hr')->translatedFormat('l') }})
            </td>
        </tr>
        <tr style="height: 50px;">
            <td class="col-label">Suglasnost voditelja/ice</td>
            <td class="col-value" style="text-align: center; vertical-align: middle;">
                @if($headOfDepartmentSignature)
                    <img src="{{ $headOfDepartmentSignature }}" class="signature-img">
                @endif
            </td>
        </tr>
        <tr style="height: 50px;">
            <td class="col-label">Suglasnost direktora/ice</td>
            <td class="col-value" style="text-align: center; vertical-align: middle;">
                @if($directorSignature)
                    <img src="{{ $directorSignature }}" class="signature-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td>
                <span class="signature-label">DATUM PODNOŠENJA</span>
                <div style="border-bottom: 1px solid #000; width: 150px; padding-bottom: 5px;">
                    {{ now()->format('d.m.Y') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
