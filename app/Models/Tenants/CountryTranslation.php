<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryTranslation extends Model
{
    protected $fillable = [
        'locale',
        'label',
        'country_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationships

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
