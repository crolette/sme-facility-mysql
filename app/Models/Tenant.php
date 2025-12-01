<?php

namespace App\Models;

use App\Enums\AddressTypes;
use App\Models\Address;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
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
        'phone_number',
        'company_code',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
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
            'phone_number',
            'company_code',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    protected $casts = [
        'data' => 'array',
    ];

    protected $appends = [
        'full_company_address',
        'full_invoice_address',
        'domain_address'
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

    public function activeSubscription()
    {
        return $this->subscriptions()->where('status', 'active')->first();
    }

    public function domainAddress(): Attribute
    {

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
    }

    public function fullCompanyAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->companyAddress->street . ', ' . $this->companyAddress->house_number . ' - ' . $this->companyAddress->zip_code . ' ' . $this->companyAddress->city . ' - ' . $this->companyAddress->country
        );
    }

    public function fullInvoiceAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->invoiceAddress ?
                $this->invoiceAddress->street . ', ' . $this->invoiceAddress->house_number . ' - ' . $this->invoiceAddress->zip_code . ' ' . $this->invoiceAddress->city . ' - ' . $this->invoiceAddress->country
                : null
        );
    }
}
