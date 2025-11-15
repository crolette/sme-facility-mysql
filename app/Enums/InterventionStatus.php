<?php

namespace App\Enums;

enum InterventionStatus: string
{
    case DRAFT = 'draft';
    case PLANNED = 'planned';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PARTS = 'waiting_parts';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
