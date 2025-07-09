<?php

namespace App\Models\Tenants;

use App\Models\Central\CategoryType;
use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    //

    protected $fillable = [
        'path',
        'name',
        'description',
        'size',
        'mime_type',
        'category_type_id',
        'documentable_type',
        'documentable_id'
    ];


    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function documentable()
    {
        return $this->morphTo();
    }
}
