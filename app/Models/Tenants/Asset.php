<?php

namespace App\Models\Tenants;

use App\Observers\AssetObserver;
use App\Models\Central\AssetType;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\Maintainable;
use App\Models\Central\AssetCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenants\ScheduledNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy([AssetObserver::class])]
class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'surface',
        'reference_code',
        'serial_number',
        'depreciable',
        "depreciation_start_date",
        "depreciation_end_date",
        "depreciation_duration",
        "residual_value",
        'brand',
        'model',
        'qr_code',
        'is_mobile',
        'qr_hash'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'location',
        'maintainable',
        'assetCategory'
    ];

    protected $appends = [
        'name',
        'description',
        'category',
        'location_route'
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'depreciable' => 'boolean',
        'residual_value' => 'decimal:2',
        'depreciation_start_date' => 'date:Y-m-d',
        'depreciation_end_date' => 'date:Y-m-d',
    ];

    // Ensure route model binding use the slug instead of ID
    public function getRouteKeyName()
    {
        return 'reference_code';
    }

    public const DEFAULT_NOTIFICATION_DELAY = 30;

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($asset) {
            $asset->notifications()->delete();

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
        return $this->morphToMany(Document::class, 'documentable')->withTimestamps();
    }

    public function contracts(): MorphToMany
    {
        return $this->morphToMany(Contract::class, 'contractable')->withTimestamps();
    }

    public function interventions(): MorphMany
    {
        return $this->morphMany(Intervention::class, 'interventionable');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(ScheduledNotification::class, 'notifiable');
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

    public function levelPath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->location->locationRoute ?? ''
        );
    }

    public function locationRoute(): Attribute
    {
        return Attribute::make(
            get: fn() => route('tenant.assets.show', $this->reference_code)
        );
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintainable->name
        );
    }

    public function manager(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintainable->manager
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

    public function getQRCodeForPdf(): Attribute
    {
        if(!$this->qr_code) {
            return Attribute::make(
                get: fn() => ''
            );
        }

        $imageData = Storage::disk('tenants')->get($this->qr_code);
        $mimeType = Storage::disk('tenants')->mimeType($this->qr_code);
        return Attribute::make(
            get: fn() => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)
        );
    }

    public function qrCodePath(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('tenants')->url($this->qr_code) ?? null
        );
    }
}
