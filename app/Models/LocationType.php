<?php

namespace App\Models;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationType extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'slug',
        'prefix',
        'level'
    ];

    protected $appends = ['label'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'translations'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }


    public static function boot()
    {
        parent::boot();

        static::deleting(function ($locationType) {
            $locationType->translations()->delete();
        });
    }

    public static function getAllCached()
    {
        return Cache::remember('location_types', 3600, function () {
            return static::with('translations')->get();
        });
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function label($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->translations->where('locale', $locale)->first()?->label ?? $this->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
