<?php

namespace App\Models\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'code',
        'qr_code',
        'surface_floor',
        'floor_material_id',
        'floor_material_other',
        'surface_walls',
        'wall_material_id',
        'wall_material_other',
        'location_type_id',
        'level_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'locationType',
        'maintainable'
    ];

    protected $appends = [
        'name',
        'description',
        'category',
        'floor_material',
        'wall_material',
    ];

    // Ensure route model binding use the slug instead of ID
    public function getRouteKeyName()
    {
        return 'reference_code';
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($floor) {
            if ($floor->rooms) {
                foreach ($floor->rooms as $room) {
                    $room->delete();
                }
            }
            $floor->maintainable()->delete();
            $floor->notifications()->delete();
        });
    }


    public function locationType(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function floorMaterialType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'floor_material_id');
    }

    public function wallMaterialType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'wall_material_id');
    }

    public function maintainable(): MorphOne
    {
        return $this->morphOne(Maintainable::class, 'maintainable');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'level_id');
    }

    public function contracts(): MorphToMany
    {
        return $this->morphToMany(Contract::class, 'contractable');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'level_id');
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

    public function notifications(): MorphMany
    {
        return $this->morphMany(ScheduledNotification::class, 'notifiable');
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

    public function floorMaterial($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->floorMaterialType ? $this->floorMaterialType->translations->where('locale', $locale)->first()?->label ?? $this->wallMaterialType->translations->where('locale', config('app.fallback_locale'))?->label : $this->floor_material_other ?? null
        );
    }

    public function wallMaterial($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->wallMaterialType ? $this->wallMaterialType->translations->where('locale', $locale)->first()?->label ?? $this->wallMaterialType->translations->where('locale', config('app.fallback_locale'))?->label : $this->wall_material_other  ?? null
        );
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintainable->name
        );
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintainable->description
        );
    }

    public function manager(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintainable->manager
        );
    }

    public function level(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->building
        );
    }
}
