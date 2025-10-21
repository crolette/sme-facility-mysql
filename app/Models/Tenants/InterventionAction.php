<?php

namespace App\Models\Tenants;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Picture;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterventionAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'intervention_date',
        'started_at',
        'finished_at',
        'intervention_costs',
        'creator_email'
    ];

    protected $with = [
        'actionType:id',
    ];

    protected $appends = [
        'type'
    ];

    protected function casts(): array
    {
        return [
            'intervention_costs' => 'decimal:2',
            'intervention_date' => 'date:Y-m-d',
            'started_at' => 'date:H:i',
            'finished_at' => 'date:H:i',
            'created_at' => 'date:Y-m-d H:i',
            'updated_at' => 'date:Y-m-d H:i',
        ];
    }

    public static function booted()
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });

        static::created(function ($action) {
            $action->intervention->updateTotalCosts();
            $action->intervention->setUpdatedAt(Carbon::now());
        });

        static::updated(function ($action) {
            $action->intervention->updateTotalCosts();
            $action->intervention->setUpdatedAt(Carbon::now());
        });

        static::deleted(function ($action) {
            $action->intervention->updateTotalCosts();
        });
    }



    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'action_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function type($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->actionType->translations->where('locale', $locale)->first()?->label ?? $this->actionType->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
