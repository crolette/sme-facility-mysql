<?php

namespace App\Models\Tenants;

use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'asset_type',
        'notification_type',
        'notification_delay_days',
        'enabled'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_delay_days' => 'integer',
            'enabled' => 'boolean',
        ];
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
