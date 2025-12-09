<?php

namespace App\Services;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Company;
use Illuminate\Support\Facades\Cache;

class TenantLimits
{
    public static function getSitesUsage()
    {
        return [
            'current' => Site::count(),
            'max' => self::getLimit('max_sites'),
            'can_create' => self::canCreateSite()
        ];
    }

    public static function getUsersUsage()
    {
        return [
            'current' => User::withoutRole('Super Admin')->where('can_login', true)->count(),
            'max' => self::getLimit('max_users'),
            'can_create' => self::canCreateLoginableUser()
        ];
    }

    public static function getStorageUsage()
    {
        return [
            'current' => Company::first()->disk_size,
            'max' => self::getLimit('max_storage_bytes'),
            'can_create' => self::canAddStorage()
        ];
    }

    // SETTER
    public static function setUsersUsage()
    {
        $tenant = tenant();
        $tenant->update(['current_users_count' => User::withoutRole('Super Admin')->where('can_login', true)->count()]);
    }

    public static function setSitesCount()
    {
        $tenant = tenant();
        $tenant->update(['current_sites_count' => Site::count()]);
    }



    // PERMISSIONS  
    public static function canCreateSite()
    {
        return self::isActive()
            && Site::count() < self::getLimit('max_sites');
    }

    public static function canCreateLoginableUser()
    {
        return self::isActive()
            && User::withoutRole('Super Admin')->where('can_login', true)->count() < self::getLimit('max_users');
    }

    public static function canAddStorage()
    {
        return self::isActive()
            && Company::first()->disk_size < self::getLimit('max_storage_bytes');
    }

    public static function canAddFile($size)
    {
        return self::isActive()
            && Company::first()->disk_size + $size < self::getLimit('max_storage_bytes');
    }

    public static function canAccessStatistics()
    {
        return self::isActive() && self::getLimit('has_statistics');
    }


    // LIMITS

    public static function getLimit($key)
    {
        $tenant = tenant();
        if (!$tenant) {
            return null;
        }

        $limits = Cache::get("tenant:{$tenant->id}:limits");
        return $limits[$key] ?? null;
    }


    protected static function getLimits(): array
    {
        $tenant = tenant();

        if (!$tenant) {
            return [];
        }

        return Cache::get("tenant:{$tenant->id}:limits")
            ?? self::loadLimitsFromDatabase($tenant);
    }

    public static function loadLimitsFromDatabase($tenant): array
    {
        // Fallback si cache manquant
        return [
            'subscription_status' => $tenant->active_subscription?->stripe_status ?? 'inactive',
            'max_sites' => $tenant->max_sites ?? 0,
            'max_users' => $tenant->max_users ?? 0,
            'max_storage_bytes' => $tenant->max_storage_gb * 1024 * 1024 ?? 0,
            'has_statistics' => $tenant->has_statistics ?? false,
        ];
    }

    protected static function isActive()
    {
        return true;
    }
}
