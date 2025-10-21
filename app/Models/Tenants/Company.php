<?php

namespace App\Models\Tenants;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'last_ticket_number',
        'last_asset_number',
        'disk_size',
        'logo',
        'address',
        'vat_number',
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'last_ticket_number',
        'last_asset_number',
    ];

    protected $appends = [
        'logo_path',
        'disk_size_mb',
        'disk_size_gb'
    ];

    public const MAX_UPLOAD_SIZE_MB = 4;

    public static function maxUploadSizeKB(): int
    {
        return self::MAX_UPLOAD_SIZE_MB * 1024;
    }

    public static function incrementDiskSize($fileSize)
    {
        DB::transaction(
            function () use ($fileSize) {
                $company = self::lockForUpdate()->first();

                $company->increment('disk_size', $fileSize);
            }
        );
    }

    public static function decrementDiskSize($fileSize)
    {
        DB::transaction(
            function () use ($fileSize) {
                $company = self::lockForUpdate()->first();

                $company->decrement('disk_size', $fileSize);
            }
        );
    }

    public static function incrementAndGetAssetNumber(): int
    {
        return DB::transaction(function () {
            $company = self::lockForUpdate()->first();

            $company->last_asset_number++;
            $company->save();

            return $company->last_asset_number;
        });
    }

    public static function incrementAndGetTicketNumber(): int
    {
        return DB::transaction(function () {
            $company = self::lockForUpdate()->first();

            $company->last_ticket_number++;
            $company->save();

            return $company->last_ticket_number;
        });
    }

    public function diskSizeMB(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->disk_size / 1024 / 1024, 2)
        );
    }

    public function diskSizeGB(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->disk_size / 1024 / 1024 / 1024, 2)
        );
    }

    public function logoPath(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('tenants')->url($this->logo) ?? null
        );
    }
}
