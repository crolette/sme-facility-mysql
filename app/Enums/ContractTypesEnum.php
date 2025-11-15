<?php

namespace App\Enums;

// Open = ticket ouvert sans intervention
// Ongoing = ticket ouvert avec au moins une intervention
// Closed = ticket résolu

enum ContractTypesEnum: string
{
    case MAINTENANCE = 'maintenance';
    case ALLIN = 'all-in';
    case INSURANCE = 'insurance';
    case CLEANING = 'cleaning';
    case ONDEMAND = 'on_demand';
    case OTHER = 'other';
}
