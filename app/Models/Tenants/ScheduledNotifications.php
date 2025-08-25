<?php

namespace App\Models\Tenants;

use App\Enums\ScheduledNotificationStatusEnum;
use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    protected $fillable = [
        'notification_type',
        'scheduled_at',
        'recipient_email',
        'recipient_name',
        'status',
        'sent_at',
        'data',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScheduledNotificationStatusEnum::class
        ];
    }


    public function notifiable()
    {
        return $this->morphTo();
    }
}
