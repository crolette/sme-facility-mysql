<?php

namespace App\Enums;

// Open = ticket ouvert sans intervention
// Ongoing = ticket ouvert avec au moins une intervention
// Closed = ticket résolu

enum ContractStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
