<?php

namespace App\Models\Tenants;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Tenants\User;
use App\Models\Tenants\Country;
use App\Models\Central\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'vat_number',
        'street',
        'house_number',
        'postal_code',
        'city',
        'logo',
        'phone_number',
        'website'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [

        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'logo_path',
        'category',
        'address'
        // 'country_label'
    ];

    public const MAX_UPLOAD_SIZE_MB = 4;

    public static function maxUploadSizeKB(): int
    {
        return self::MAX_UPLOAD_SIZE_MB * 1024;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function maintainables(): BelongsToMany
    {
        return $this->belongsToMany(Maintainable::class, 'provider_maintainable');
    }

    public function providerCategory(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function assets()
    {
        // not trashed assets
        return $this->maintainables()
            ->where('maintainable_type', Asset::class)
            ->with('maintainable')
            ->whereHas('maintainable');
    }

    public function locations()
    {
        return $this->maintainables()
            ->whereNot('maintainable_type', Asset::class)->with(['maintainable']);
    }

    public function category($locale = null): Attribute
    {
        $locale = $locale ?? app()->getLocale();

        return Attribute::make(
            get: fn() => $this->providerCategory?->translations->where('locale', $locale)->first()?->label ?? $this->providerCategory?->translations->where('locale', config('app.fallback_locale'))?->label ?? ''
        );
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->street . ' ' . ($this->house_number ?? '') . ' - ' . $this->postal_code . ' ' . $this->city . ' - ' . $this->country->label
        );
    }

    public function logoPath(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('tenants')->url($this->logo) ?? null
        );
    }
}
