<?php

namespace App\Models\Tenants;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Tenants\Asset;
use App\Observers\UserObserver;
use App\Models\Tenants\Provider;
use App\Models\Tenants\Intervention;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    public $afterCommit = true;
    protected string $guard_name = 'tenant';

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'job_position',
        'phone_number',
        'can_login',
        'preferred_locale'

    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_login' => 'boolean'
        ];
    }

    public static function booted(): void
    {
        parent::boot();

        static::deleting(function ($user) {
            $notifications = $user->notifications()->where('status', 'pending')->get();
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        });
    }

    public const MAX_UPLOAD_SIZE_MB = 4;

    public static function maxUploadSizeKB(): int
    {
        return self::MAX_UPLOAD_SIZE_MB * 1024;
    }

    public function maintainables(): BelongsToMany
    {
        return $this->belongsToMany(Maintainable::class, 'user_maintainable');
    }

    public function manager(): HasMany
    {
        return $this->hasMany(Maintainable::class, 'maintenance_manager_id');
    }

    public function notification_preferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'location');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ScheduledNotification::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function assignedInterventions(): MorphMany
    {
        return $this->morphMany(Intervention::class, 'assignable');
    }

    public function locationRoute(): Attribute
    {
        return Attribute::make(
            get: fn() => route('tenant.users.show', $this->id)
        );
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->first_name . ' ' . $this->last_name
        );
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function setLocale(string $locale)
    {
        $this->update([
            'preferred_locale' => $locale,
        ]);
    }
}
