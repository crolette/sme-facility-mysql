<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Intervention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
