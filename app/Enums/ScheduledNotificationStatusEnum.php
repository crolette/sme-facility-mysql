<?php

namespace App\Enums;

enum ScheduledNotificationStatusEnum: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
