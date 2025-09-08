<?php

namespace App\Models\Tenants;

use App\Enums\ScheduledNotificationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledNotification extends Model
{
    protected $fillable = [
        'notification_type',
        'scheduled_at',
        'recipient_email',
        'recipient_name',
        'retry_count',
        'error_message',
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
            'data' => 'array',
            'scheduled_at' => 'date',
            'sent_at' => 'date',
            'status' => ScheduledNotificationStatusEnum::class
        ];
    }


    public function notifiable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
