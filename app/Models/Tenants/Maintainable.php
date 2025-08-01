<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Intervention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

        'maintainable_type',
        'maintainable_id'
    ];


    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date:Y-m-d',
            'end_warranty_date' => 'date:Y-m-d',
            'under_warranty' => 'boolean'
        ];
    }


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
