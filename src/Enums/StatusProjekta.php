<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusProjekta: string implements HasLabel, HasColor
{
    /**
     * *Faze svakog projekta su:**
     * 2. **Priprema:** Faza koja počinje nakon prihvaćanja ponude (izrada izvedbene dokumentacije, specifikacija materijala).
     * 3. **Provedba:** Faza koja uključuje narudžbu materijala, pripremu za montažu (radni nalog) i samu montažu.
     * 4. **Finalizacija:** Faza koja uključuje završnu dokumentaciju i financijski obračun (fakturiranje).
    */
    case Priprema = 'Priprema';
    case Provedba = 'Provedba';
    case Finalizacija = 'Finalizacija';
    case Arhiviran = 'Arhiviran';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Priprema => 'Priprema',
            self::Provedba => 'Montaža',
            self::Finalizacija => 'Završetak projekta',
            self::Arhiviran => 'Arhiviran',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Priprema => 'warning',
            self::Provedba => 'primary',
            self::Finalizacija => 'success',
            self::Arhiviran => 'gray',
        };
    }
}
