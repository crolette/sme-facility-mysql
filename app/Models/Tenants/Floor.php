<?php

namespace App\Models\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Document;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
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

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'level_id');
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
}
