<?php

namespace App\Models\Tenants;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Tenants\Provider;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected string $guard_name = 'tenant';
    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'can_login'

    ];

    protected $appends = [
        'full_name'
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

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->first_name . ' ' . $this->last_name
        );
    }
}
