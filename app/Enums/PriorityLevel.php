<?php

namespace App\Enums;

enum PriorityLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function order(): int
    {
        return match ($this) {
            self::LOW => 4,
            self::MEDIUM => 3,
            self::HIGH => 2,
            self::URGENT => 1,
        };
    }
}
