<?php

namespace App\Models\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Document;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Building extends Model
{
    use HasFactory;


    protected $fillable = [
        'reference_code',
        'code',
        'location_type_id',
        'level_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'locationType',
        'maintainable',
    ];

    protected $appends = [
        'category',
    ];

    public static function boot()
    {
        parent::boot();

        // Lors de la suppression d'un building, on va supprimer tous les floors liés. 
        static::deleting(function ($building) {
            if ($building->floors) {
                foreach ($building->floors as $floor) {
                    $floor->delete();
                }
            }
            $building->maintainable()->delete();
        });
    }


    public function locationType(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function maintainable(): MorphOne
    {
        return $this->morphOne(Maintainable::class, 'maintainable');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'level_id');
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class, 'level_id');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'location');
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->locationType->translations->where('locale', $locale)->first()?->label ?? $this->locationType->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
