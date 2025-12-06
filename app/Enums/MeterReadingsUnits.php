<?php

namespace App\Enums;

enum MeterReadingsUnits: string
{
    case KWH = 'kWh';
    case M3 = 'm3';
    case LITER = 'l';
    case KM = 'km';
}
