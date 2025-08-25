<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\ContractStatusEnum;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'internal_reference',
        'provider_reference',
        'start_date',
        'end_date',
        'renewal_type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'date:d-m-Y',
            'updated_at' => 'date:d-m-Y',
            'renewal_type' => ContractRenewalTypesEnum::class,
            'status' => ContractStatusEnum::class
        ];
    }

    public const DEFAULT_NOTIFICATION_DELAY = 30;

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function assets()
    {
        return $this->morphedByMany(Asset::class, 'contractable');
    }

    public function sites()
    {
        return $this->morphedByMany(Site::class, 'contractable');
    }

    public function buildings()
    {
        return $this->morphedByMany(Building::class, 'contractable');
    }

    public function floors()
    {
        return $this->morphedByMany(Floor::class, 'contractable');
    }

    public function rooms()
    {
        return $this->morphedByMany(Room::class, 'contractable');
    }

    /**
     * contractable
     *
     * @return MorphTo
     */
    public function contractables(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function getObjects($columns = ['id', 'code', 'reference_code', 'category_type_id', 'location_type_id'])
    {
        // if (!$this->relationLoaded('assets')) {
        $this->loadObjectsWithColumns($columns);
        // }

        return collect()
            ->merge($this->assets)
            ->merge($this->sites)
            ->merge($this->buildings)
            ->merge($this->floors)
            ->merge($this->rooms);
    }

    public function loadObjectsWithColumns($columns = ['id', 'code', 'reference_code', 'category_type_id'])
    {
        if (!in_array('id', $columns)) {
            array_unshift($columns, 'id');
        }

        $assetColumns = array_filter($columns, function ($value) {
            return $value != 'location_type_id';
        });
        $locationColumns = array_filter($columns, function ($value) {
            return $value != 'category_type_id';
        });

        $this->load([
            'assets:' . implode(',', $assetColumns),
            'sites:' . implode(',', $locationColumns),
            'buildings:' . implode(',', $locationColumns),
            'floors:' . implode(',', $locationColumns),
            'rooms:' . implode(',', $locationColumns),
        ]);

        return $this;
    }


    public function scopeWithObjects($query, $columns = ['id', 'code', 'reference_code'])
    {
        // Assure-toi d'inclure l'id pour les relations
        if (!in_array('id', $columns)) {
            array_unshift($columns, 'id');
        }

        $columnString = implode(',', $columns);

        return $query->with([
            'assets:' . $columnString,
            'sites:' . $columnString,
            'buildings:' . $columnString,
            'floors:' . $columnString,
            'rooms:' . $columnString,
        ]);
    }
}
