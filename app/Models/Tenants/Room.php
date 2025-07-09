<?php

namespace App\Models\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
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
}
