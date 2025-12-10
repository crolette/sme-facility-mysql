<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Document;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Observers\ContractObserver;
use App\Models\Tenants\Contractable;
use Illuminate\Support\Facades\Auth;
use App\Enums\ContractRenewalTypesEnum;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenants\ScheduledNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy([ContractObserver::class])]
class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'internal_reference',
        'provider_reference',
        'start_date',
        'contract_duration',
        'end_date',
        'notice_date',
        'notice_period',
        'renewal_type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'date:Y-m-d',
            'updated_at' => 'date:Y-m-d',
            'notice_date' => 'date:Y-m-d',
            'end_date' => 'immutable_date:Y-m-d',
            'start_date' => 'date:Y-m-d',
            'type' => ContractTypesEnum::class,
            'notice_period' => NoticePeriodEnum::class,
            'contract_duration' => ContractDurationEnum::class,
            'renewal_type' => ContractRenewalTypesEnum::class,
            'status' => ContractStatusEnum::class
        ];
    }

    protected $with = [
        'provider'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($contract) {
            $contract->notifications()->delete();
        });
    }

    public const DEFAULT_NOTIFICATION_DELAY = 7;

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function assets()
    {
        return $this->morphedByMany(Asset::class, 'contractable')->using(Contractable::class)->withTimestamps();
    }

    public function sites()
    {
        return $this->morphedByMany(Site::class, 'contractable')->using(Contractable::class)->withTimestamps();
    }

    public function buildings()
    {
        return $this->morphedByMany(Building::class, 'contractable')->using(Contractable::class)->withTimestamps();
    }

    public function floors()
    {
        return $this->morphedByMany(Floor::class, 'contractable')->using(Contractable::class)->withTimestamps();
    }

    public function rooms()
    {
        return $this->morphedByMany(Room::class, 'contractable')->using(Contractable::class)->withTimestamps();
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
    }


    public function contractables()
    {
        return $this->hasMany(Contractable::class);
    }

    // public function contractables()
    // {
    //     // return $this->morphTo()->withTrashed();
    //     return $this->assets
    //         ->concat($this->sites)
    //         ->concat($this->buildings)
    //         ->concat($this->floors)
    //         ->concat($this->rooms);
    // }


    public function scopeForMaintenanceManager(Builder $query, ?User $user = null)
    {
        $user = $user ?? Auth::user();

        if ($user?->hasRole('Maintenance Manager')) {
            $query->whereHas('assets.maintainable', function (Builder $query) use ($user) {
                $query->where('maintenance_manager_id', $user->id);
            })->orWhereHas('rooms.maintainable', function (Builder $query) use ($user) {
                $query->where('maintenance_manager_id', $user->id);
            })->orWhereHas('floors.maintainable', function (Builder $query) use ($user) {
                $query->where('maintenance_manager_id', $user->id);
            })->orWhereHas('buildings.maintainable', function (Builder $query) use ($user) {
                $query->where('maintenance_manager_id', $user->id);
            })->orWhereHas('sites.maintainable', function (Builder $query) use ($user) {
                $query->where('maintenance_manager_id', $user->id);
            });
        }
    }


    public function notifications(): MorphMany
    {
        return $this->morphMany(ScheduledNotification::class, 'notifiable');
    }

    public function directory(): Attribute
    {
        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/contracts/" . $this->id . "/";

        return Attribute::make(
            get: fn() => $directory
        );
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
