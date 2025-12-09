<?php

namespace App\Models;

use App\Enums\AddressTypes;
use App\Models\Central\CentralCountry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{

    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'street',
        'house_number',
        'zip_code',
        'city',
        'address_type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(CentralCountry::class);
    }

    public function tenantCompanyAddress(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->where('address_type', AddressTypes::COMPANY);
    }

    public function tenantInvoiceAddress(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->where('address_type', AddressTypes::INVOICE);
    }
}
