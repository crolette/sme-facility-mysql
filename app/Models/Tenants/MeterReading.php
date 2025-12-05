<?php

namespace App\Models\Tenants;

use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'meter',
        'meter_date',
        'notes'
    ];

    protected $casts = [
        'meter' => 'decimal:2',
    ];


    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
