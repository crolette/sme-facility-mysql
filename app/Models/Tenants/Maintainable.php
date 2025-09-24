<?php

namespace App\Models\Tenants;

use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Intervention;
use App\Observers\MaintainableObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([MaintainableObserver::class])]
class Maintainable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'purchase_date',
        'purchase_cost',
        'under_warranty',
        'end_warranty_date',

        'need_maintenance',
        'maintenance_frequency',
        'next_maintenance_date',
        'last_maintenance_date',

        'maintainable_type',
        'maintainable_id'
    ];


    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        // 'manager',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date:Y-m-d',
            'end_warranty_date' => 'date:Y-m-d',
            'under_warranty' => 'boolean',
            'need_maintenance' => 'boolean',
            'next_maintenance_date' => 'date:Y-m-d',
            'last_maintenance_date' => 'date:Y-m-d',
            'maintenance_frequecy' => MaintenanceFrequency::class
        ];
    }

    public const DEFAULT_NOTIFICATION_DELAY = 30;


    public function maintainable()
    {
        return $this->morphTo();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_maintainable');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maintenance_manager_id');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_maintainable');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
    
}
