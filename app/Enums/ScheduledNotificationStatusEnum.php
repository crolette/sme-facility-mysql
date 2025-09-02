<?php

namespace App\Enums;

enum ScheduledNotificationStatusEnum: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case ERROR = 'error';
}
