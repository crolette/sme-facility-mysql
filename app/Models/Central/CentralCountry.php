<?php

namespace App\Models\Central;

use App\Models\Tenants\Provider;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenants\CountryTranslation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentralCountry extends Model
{
    protected $connection = 'central';
    protected $table = "countries";

    protected $fillable = [
        'iso_code_a3',
        'iso_code_a2',
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];



    // Relationships

}
