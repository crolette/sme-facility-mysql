<?php

namespace App\Models\Tenants;

use App\Models\Tenants\User;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected function casts(): array
    {
        return [
            'intervention_date' => 'date:d-m-Y',
            'created_at' => 'date:d-m-Y H:m',
            'updated_at' => 'date:d-m-Y H:m',
        ];
    }

    public static function booted()
    {
        static::created(function ($action) {
            $action->intervention->updateTotalCosts();
        });

        static::updated(function ($action) {
            $action->intervention->updateTotalCosts();
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
}
