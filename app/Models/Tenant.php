<?php

namespace App\Models;

use App\Models\Address;
use App\Enums\AddressTypes;
use Laravel\Cashier\Billable;
use App\Models\Central\Subscription;
use App\Models\Central\CentralCountry;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;


class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFactory, Billable;

    protected $connection = 'central';

    protected $fillable = [
        'id',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'vat_number',
        'verified_vat_status',
        'phone_number',
        'company_code',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'max_sites',
        'max_users',
        'max_storage_gb',
        'has_statistics',
        'current_sites_count',
        'current_users_count',
        'current_storage_bytes',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Due to PostGreSQL, you have to declare what columns are "real" columns and not data stored columns
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'first_name',
            'last_name',
            'email',
            'vat_number',
            'verified_vat_status',
            'phone_number',
            'company_code',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
            'max_sites',
            'max_users',
            'max_storage_gb',
            'has_statistics',
            'current_sites_count',
            'current_users_count',
            'current_storage_bytes',
        ];
    }

    protected $casts = [
        'data' => 'array',
        'trial_ends_at' => 'date:d-m-Y',
        'has_statistics' => 'boolean',
    ];

    protected $appends = [
        'full_company_address',
        'full_invoice_address',
        'domain_address',
        'active_subscription',
        'has_active_subscription',
        'disk_size_mb',
        'disk_size_gb'
    ];

    public function companyAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('address_type', AddressTypes::COMPANY->value);
    }

    public function invoiceAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('address_type', AddressTypes::INVOICE->value);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->subscriptions()->where('stripe_status', 'trialing')->orWhere('stripe_status', 'active')->first() ?? null
        );
    }

    public function hasActiveSubscription(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->subscriptions()->where('stripe_status', 'trialing')->orWhere('stripe_status', 'active')->first() ? true : false
        );
    }

    public function domainAddress(): Attribute
    {
        if ($this->domain?->domain) {
            if (str_starts_with(config('app.url'), 'https://')) {
                $suffix = substr(config('app.url'), strlen('https://'));
                $address = preg_replace('/^https?:\/\/[^\/]+/', "https://{$this->domain->domain}" . '.' . $suffix, config('app.url'));
            } else {
                $suffix = substr(config('app.url'), strlen('http://'));
                $address = preg_replace('/^http?:\/\/[^\/]+/', "http://{$this->domain->domain}" . '.' . $suffix, config('app.url'));
            }
            return Attribute::make(
                get: fn() => $address
            );
        } else {
            return Attribute::make(
                get: fn() => null
            );
        }
    }

    public function diskSizeMb(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->current_storage_bytes / 1024 / 1024, 2)
        );
    }

    public function diskSizeGb(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->current_storage_bytes / 1024 / 1024 / 1024, 2)
        );
    }

    public function fullCompanyAddress(): Attribute
    {
        if ($this->companyAddress) {

            $country = CentralCountry::where('iso_code_a2', $this->companyAddress->country)->first();

            return Attribute::make(
                get: fn() => $this->companyAddress->street . ', ' . $this->companyAddress->house_number . ' - ' . $this->companyAddress->zip_code . ' ' . $this->companyAddress->city . ' - ' . $country->name
            );
        } else {
            return Attribute::make(get: fn() => null);
        }
    }

    public function fullInvoiceAddress(): Attribute
    {
        if ($this->invoiceAddress) {

            $country = CentralCountry::where('iso_code_a2', $this->invoiceAddress->country)->first();

            return Attribute::make(
                get: fn() => $this->invoiceAddress ?
                    $this->invoiceAddress->street . ', ' . $this->invoiceAddress->house_number . ' - ' . $this->invoiceAddress->zip_code . ' ' . $this->invoiceAddress->city . ' - ' . $country->name
                    : null
            );
        } else {
            return Attribute::make(get: fn() => null);
        }
    }
}
