<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

enum CroatianMonth: int
{
    case Sijecanj = 1;
    case Veljaca = 2;
    case Ozujak = 3;
    case Travanj = 4;
    case Svibanj = 5;
    case Lipanj = 6;
    case Srpanj = 7;
    case Kolovoz = 8;
    case Rujan = 9;
    case Listopad = 10;
    case Studeni = 11;
    case Prosinac = 12;

    public function label(): string
    {
        return match ($this) {
            self::Sijecanj => 'Siječanj',
            self::Veljaca => 'Veljača',
            self::Ozujak => 'Ožujak',
            self::Travanj => 'Travanj',
            self::Svibanj => 'Svibanj',
            self::Lipanj => 'Lipanj',
            self::Srpanj => 'Srpanj',
            self::Kolovoz => 'Kolovoz',
            self::Rujan => 'Rujan',
            self::Listopad => 'Listopad',
            self::Studeni => 'Studeni',
            self::Prosinac => 'Prosinac',
        };
    }

    /**
     * ASCII-safe naziv za filenamove (bez dijakritika).
     */
    public function asciiLabel(): string
    {
        return match ($this) {
            self::Sijecanj => 'Sijecanj',
            self::Veljaca => 'Veljaca',
            self::Ozujak => 'Ozujak',
            self::Travanj => 'Travanj',
            self::Svibanj => 'Svibanj',
            self::Lipanj => 'Lipanj',
            self::Srpanj => 'Srpanj',
            self::Kolovoz => 'Kolovoz',
            self::Rujan => 'Rujan',
            self::Listopad => 'Listopad',
            self::Studeni => 'Studeni',
            self::Prosinac => 'Prosinac',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $m) => $m->label(), self::cases()),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function asciiLabels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $m) => $m->asciiLabel(), self::cases()),
        );
    }
}
