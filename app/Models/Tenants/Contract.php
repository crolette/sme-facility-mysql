<?php

namespace App\Models\Tenants;

use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Central\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'start_date' => 'date:d-m-Y',
            'end_date' => 'date:d-m-Y',
            'created_at' => 'date:d-m-Y',
            'updated_at' => 'date:d-m-Y',
            'renewal_type' => ContractRenewalTypesEnum::class,
            'status' => ContractStatusEnum::class
        ];
    }

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
}
