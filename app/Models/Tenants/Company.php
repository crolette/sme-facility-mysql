<?php

namespace App\Models\Tenants;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    protected $fillable = [
        'last_ticket_number',
        'last_asset_number',
    ];



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
}
