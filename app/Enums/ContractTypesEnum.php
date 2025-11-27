<?php

namespace App\Enums;

enum ContractTypesEnum: string
{
    case MAINTENANCE = 'maintenance';
    case ALLIN = 'all-in';
    case INSURANCE = 'insurance';
    case CLEANING = 'cleaning';
    case ONDEMAND = 'on_demand';
    case OTHER = 'other';
}
