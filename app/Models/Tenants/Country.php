<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Provider;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenants\CountryTranslation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'iso_code',
        'name',
    ];

    protected $appends = ['label'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationships

    public function translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function label($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->translations->where('locale', $locale)->first()?->label ?? $this->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
