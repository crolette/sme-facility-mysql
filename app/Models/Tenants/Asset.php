<?php

namespace App\Models\Tenants;

use App\Models\Central\AssetType;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\Maintainable;
use App\Models\Central\AssetCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'surface',
        'reference_code',
        'serial_number',
        'brand',
        'model',
        'qr_code',
        'is_mobile'
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
        'name',
        'description',
        'category',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
    ];

    // Ensure route model binding use the slug instead of ID
    public function getRouteKeyName()
    {
        return 'reference_code';
    }



    public static function boot()
    {
        parent::boot();

        static::deleting(function ($asset) {
            $tickets = $asset->tickets;
            foreach ($tickets as $ticket) {
                $ticket->closeTicket();
            }
        });

        static::forceDeleting(function ($asset) {
            $asset->maintainable()->delete();
            $asset->tickets()->delete();
        });
    }

    public function maintainable(): MorphOne
    {
        return $this->morphOne(Maintainable::class, 'maintainable');
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
    }

    public function interventions(): MorphMany
    {
        return $this->morphMany(Intervention::class, 'interventionable');
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'ticketable');
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->assetCategory->translations->where('locale', $locale)->first()?->label ?? $this->assetCategory->translations->where('locale', config('app.fallback_locale'))?->label
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

    public function ownInterventions(): HasMany
    {
        return $this->interventions()->where('ticket_id', null);
    }



    public function qrCodePath(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('tenants')->url($this->qr_code) ?? null
        );
    }
}
