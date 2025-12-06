<?php

namespace App\Models\Tenants;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Models\Central\CategoryType;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryProvider extends Pivot
{
    protected $connection = 'tenant';
    protected $table = 'category_type_provider';


    // public function provider(): BelongsTo
    // {
    //     return $this->belongsTo(Provider::class, 'provider_id');
    // }

    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(CategoryType::class, 'category_id');
    // }
}
