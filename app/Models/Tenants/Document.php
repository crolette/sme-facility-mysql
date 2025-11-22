<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'filename',
        'directory',
        'name',
        'description',
        'size',
        'mime_type',
        'category_type_id',
    ];

    protected $appends = [
        'category',
        'fullPath',
        'sizeMo'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'date:Y-m-d',
            'updated_at' => 'date:Y-m-ds',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($document) {
            Company::incrementDiskSize($document->size);
        });

        static::deleted(function ($document) {
            Company::decrementDiskSize($document->size);
        });
    }

    public const MAX_UPLOAD_SIZE_MB = 4;

    public static function maxUploadSizeKB(): int
    {
        return self::MAX_UPLOAD_SIZE_MB * 1024;
    }


    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function assets()
    {
        return $this->morphedByMany(Asset::class, 'documentable');
    }

    public function sites()
    {
        return $this->morphedByMany(Site::class, 'documentable');
    }

    public function buildings()
    {
        return $this->morphedByMany(Building::class, 'documentable');
    }

    public function floors()
    {
        return $this->morphedByMany(Floor::class, 'documentable');
    }

    public function rooms()
    {
        return $this->morphedByMany(Room::class, 'documentable');
    }

    public function getDocumentablesFlat()
    {
        return collect()
            ->merge($this->assets)
            ->merge($this->sites)
            ->merge($this->buildings)
            ->merge($this->floors)
            ->merge($this->rooms);
    }

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


    public function getFullPathAttribute()
    {
        return Storage::disk('tenants')->url($this->path);
    }

    public function getSizeMoAttribute()
    {

        return round($this->size / 1024 / 1024, 2);
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->documentCategory->translations->where('locale', $locale)->first()?->label ?? $this->documentCategory->translations->where('locale', config('app.fallback_locale'))?->label
        );
    }
}
