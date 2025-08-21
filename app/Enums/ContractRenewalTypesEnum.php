<?php

namespace App\Enums;

// Open = ticket ouvert sans intervention
// Ongoing = ticket ouvert avec au moins une intervention
// Closed = ticket résolu

enum ContractRenewalTypesEnum: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';
}
