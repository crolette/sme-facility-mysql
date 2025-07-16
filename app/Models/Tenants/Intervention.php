<?php

namespace App\Models\Tenants;

use App\Enums\PriorityLevel;
use App\Models\Tenants\Ticket;
use App\Enums\InterventionStatus;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority',
        'status',
        'planned_at',
        'description',
        'repair_delay'
    ];

    protected $with = [
        'interventionType',
        'actions'
    ];

    protected function casts(): array
    {
        return [
            'planned_at' => 'date:d-m-Y',
            'created_at' => 'date:d-m-Y H:m',
            'updated_at' => 'date:d-m-Y H:m',
            'status' => InterventionStatus::class,
            'priority' => PriorityLevel::class
        ];
    }

    public function actions(): HasMany
    {
        return $this->hasMany(InterventionAction::class);
    }

    public function interventionType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'intervention_type_id');
    }

    public function maintainable(): BelongsTo
    {
        return $this->belongsTo(Maintainable::class);
    }

    // Asset, Site, Building, Floor, Room
    public function interventionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
