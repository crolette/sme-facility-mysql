<?php

namespace App\Models\Tenants;

use App\Models\Central\AssetType;
use App\Models\Tenants\Maintainable;
use App\Models\Central\AssetCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'reference_code',
        'serial_number',
        'brand',
        'model'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'location',
        'maintainable',
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

        static::forceDeleting(function ($asset) {
            $asset->maintainable()->delete();
        });
    }

    public function maintainable(): MorphOne
    {
        return $this->morphOne(Maintainable::class, 'maintainable');
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->assetCategory->translations->where('locale', $locale)->first()?->label ?? $this->assetCategory->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
