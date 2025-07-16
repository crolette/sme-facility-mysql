<?php

namespace App\Enums;

enum InterventionStatus: string
{
    case DRAFT = 'draft';
    case PLANNED = 'planned';
    case IN_PROGRESS = 'in progress';
    case WAITING_PARTS = 'waiting for parts';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
