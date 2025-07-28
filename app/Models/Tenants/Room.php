<?php

namespace App\Models\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Document;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'surface_floor',
        'surface_walls',
        'reference_code'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'level_id',
        'location_type_id'

    ];

    protected $with = [
        'locationType',
        'maintainable'
    ];

    protected $appends = [
        'category',
    ];

    // Ensure route model binding use the slug instead of ID
    public function getRouteKeyName()
    {
        return 'code';
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($room) {
            $room->maintainable()->delete();
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

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'level_id');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'location');
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
    }

    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'ticketable');
    }

    public function interventions(): MorphMany
    {
        return $this->morphMany(Intervention::class, 'interventionable');
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->locationType->translations->where('locale', $locale)->first()?->label ?? $this->locationType->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
