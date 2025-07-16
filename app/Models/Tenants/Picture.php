<?php

namespace App\Models\Tenants;

use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'filename',
        'directory',
        'mime_type',
        'size',
        'uploader_email',
    ];

    protected $appends = [
        'fullPath',
        'sizeMo'
    ];

    public const MAX_UPLOAD_SIZE_MB = 4;

    public static function maxUploadSizeKB(): int
    {
        return self::MAX_UPLOAD_SIZE_MB * 1024;
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFullPathAttribute()
    {
        return Storage::disk('tenants')->url($this->path);
    }

    public function getSizeMoAttribute()
    {
        return round($this->size / 1024 / 1024, 2);
    }
}
