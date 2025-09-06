<?php

namespace Amicus\FilamentEmployeeManagement\Enums;

enum LogType: string
{
    case RADNI_SATI = 'radni_sati';
    case BOLOVANJE = 'bolovanje';
    case GODISNJI = 'godisnji';
    case PLACENI_SLOBODAN_DAN = 'placeni_slobodan_dan';

    public function getLabel(): string
    {
        return match ($this) {
            self::RADNI_SATI => 'Radni sati',
            self::BOLOVANJE => 'Bolovanje',
            self::GODISNJI => 'Godišnji odmor',
            self::PLACENI_SLOBODAN_DAN => 'Plaćeni slobodan dan',
        };
    }
}