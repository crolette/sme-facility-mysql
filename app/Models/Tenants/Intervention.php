<?php

namespace App\Models\Tenants;

use App\Enums\PriorityLevel;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Picture;
use App\Enums\InterventionStatus;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use App\Observers\InterventionObserver;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenants\ScheduledNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([InterventionObserver::class])]
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
        'assignable',
        'actions'
    ];

    protected $appends = [
        'type'
    ];


    protected function casts(): array
    {
        return [
            'total_costs' => 'decimal:2',
            'planned_at' => 'date:Y-m-d',
            'repair_delay' => 'date:Y-m-d',
            'created_at' => 'date:Y-m-d',
            'updated_at' => 'date:Y-m-d',
            'status' => InterventionStatus::class,
            'priority' => PriorityLevel::class
        ];
    }

    public static function booted(): void
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });

        static::created(function ($intervention) {
            $intervention->ticket?->changeStatusToOngoing();
        });

        static::deleted(function ($intervention) {
            $intervention->notifications()->delete();
        });
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

    public function notifications(): MorphMany
    {
        return $this->morphMany(ScheduledNotification::class, 'notifiable');
    }

    // Asset, Site, Building, Floor, Room
    public function interventionable(): MorphTo
    {
        return $this->morphTo();
        // return $this->morphTo()->withTrashed();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }


    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }


    public function updateTotalCosts(): void
    {
        $this->total_costs = $this->actions()->sum('intervention_costs');
        $this->save();
    }


    public function type($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->interventionType->translations->where('locale', $locale)->first()?->label ?? $this->interventionType->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }

    public function scopeOrderByPriority($query, $direction = 'asc')
    {
        $order = $direction === 'asc'
            ? PriorityLevel::cases()
            : array_reverse(PriorityLevel::cases());

        $cases = collect($order)->map(
            fn($priority, $index) =>
            "WHEN priority = '{$priority->value}' THEN " . ($index + 1)
        )->join(' ');

        return $query->orderByRaw("CASE {$cases} END");
    }
}
