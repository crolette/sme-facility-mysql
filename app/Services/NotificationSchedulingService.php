<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;

class NotificationSchedulingService
{

    public function updateScheduleOfUserForNotificationType(UserNotificationPreference $preference)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


        if ($preference->wasChanged('notification_delay_days')) {
            match ($preference->notification_type) {
                'notice_date'  => $this->updateScheduleForContractNoticeDate($preference),
                'end_date'  => $this->updateScheduleForContractEndDate($preference),
                'end_warranty_date' => $this->updateScheduleForEndWarrantyDate($preference),
                'depreciation_end_date' => $this->updateScheduleForDepreciationEndDate($preference),
                'next_maintenance_date' => $this->updateScheduleForNextMaintenanceDate($preference),
                'planned_at' => $this->updateScheduleForPlannedAtDate($preference),
                default => null
            };
        };

        if ($preference->wasChanged('enabled') && $preference->enabled === false) {
            $this->deleteScheduledNotificationForNotificationType($preference);
        }


        if ($preference->wasChanged('enabled') && $preference->enabled === true) {
            match ($preference->notification_type) {
                'notice_date'  => $this->createScheduleForContractNoticeDate($preference),
                'end_date'  => $this->createScheduleForContractEndDate($preference),
                'end_warranty_date' => $this->createScheduleForWarrantyEndDate($preference),
                'depreciation_end_date' => $this->createScheduleForDepreciationEndDate($preference),
                'next_maintenance_date' => $this->createScheduleForNextMaintenanceDate($preference),
                'planned_at' => $this->createScheduleForPlannedAtDate($preference),
                default => null
            };
        }
    }

    public function updateScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {

            if ($notification->notifiable->maintainable->maintenance_frequency == MaintenanceFrequency::ONDEMAND->value || $notification->notifiable->maintainable->next_maintenance_date->subDays($preference->notification_delay_days) < now())
                continue;

            $newDate = $notification->notifiable->maintainable->next_maintenance_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->depreciation_end_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }


    public function updateScheduleForEndWarrantyDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->maintainable->end_warranty_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForContractNoticeDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->notice_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }


    public function updateScheduleForContractEndDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->end_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForPlannedAtDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->planned_at->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }


    public function deleteScheduledNotificationForNotificationType(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $notification->delete();
        }
    }

    public function createScheduleForPlannedAtDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $interventions = Intervention::where('planned_at', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($interventions as $intervention) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $intervention->planned_at,
                    'link' => route('tenant.interventions.show', $intervention->id)
                ]
            ];

            $createdNotification = $intervention->notifications()->create([
                ...$notification,
                'scheduled_at' => $intervention->planned_at->subDays($delayDays),
                'notification_type' => 'planned_at',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }

    public function createScheduleForContractNoticeDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('notice_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $contract->notice_date,
                    'link' => route('tenant.contracts.show', $contract->id)
                ]
            ];

            $createdNotification = $contract->notifications()->create([
                ...$notification,
                'scheduled_at' => $contract->notice_date->subDays($delayDays),
                'notification_type' => 'notice_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function createScheduleForContractEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('end_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $contract->end_date,
                    'link' => route('tenant.contracts.show', $contract->id)
                ]
            ];

            $createdNotification = $contract->notifications()->create([
                ...$notification,
                'scheduled_at' => $contract->end_date->subDays($delayDays),
                'notification_type' => 'end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function createScheduleForWarrantyEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;

        $assetsOrLocations = collect()
            ->merge($this->searchEntity(Asset::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Site::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Building::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Floor::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Room::class, 'end_warranty_date', $delayDays));

        // $assets = Asset::whereHas('maintainable', fn($query) => $query->where('end_warranty_date', '>', Carbon::now()->addDays($delayDays)))->get();
        $user = $preference->user;

        foreach ($assetsOrLocations as $assetOrLocation) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $assetOrLocation->maintainable->end_warranty_date,
                    'link' => match ($assetOrLocation->maintainable->maintainable_type) {
                        'App\Models\Tenants\Site' => route('tenant.sites.show', $assetOrLocation->reference_code),
                        'App\Models\Tenants\Building' => route('tenant.buildings.show', $assetOrLocation->reference_code),
                        'App\Models\Tenants\Floor' => route('tenant.floors.show', $assetOrLocation->reference_code),
                        'App\Models\Tenants\Room' => route('tenant.rooms.show', $assetOrLocation->reference_code),
                        'App\Models\Tenants\Asset' => route('tenant.assets.show', $assetOrLocation->reference_code),
                    }
                ]
            ];

            $assetOrLocation->notifications()->create([
                ...$notification,
                'scheduled_at' => $assetOrLocation->maintainable->end_warranty_date->subDays($delayDays),
                'notification_type' => 'end_warranty_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
        }
    }


    public function createScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assets = Asset::where('depreciation_end_date', '>', Carbon::now()->addDays($delayDays))->get();
        $user = $preference->user;

        foreach ($assets as $asset) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $asset->end_date,
                    'link' => route('tenant.assets.show', $asset->reference_code)
                ]
            ];

            $asset->notifications()->create([
                ...$notification,
                'scheduled_at' => $asset->depreciation_end_date->subDays($delayDays),
                'notification_type' => 'depreciation_end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
        }
    }

    public function createScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {
        // dump('-- createScheduleForNextMaintenanceDate');
        // dump($preference->user);
        $delayDays = $preference->notification_delay_days;
        $assetsOrLocations = collect([]);

        $assetsOrLocations = collect()
            ->merge($this->searchEntity(Asset::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Site::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Building::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Floor::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Room::class, 'next_maintenance_date', $delayDays));

        $user = $preference->user;

        foreach ($assetsOrLocations as $assetOrLocation) {
            if ($preference && $preference->enabled && $assetOrLocation->maintainable->maintenance_frequency != MaintenanceFrequency::ONDEMAND->value &&   $assetOrLocation->maintainable->next_maintenance_date->subDays($preference->notification_delay_days) > now()) {
                $notification = [
                    'status' => ScheduledNotificationStatusEnum::PENDING->value,
                    'data' => [
                        'subject' => 'test',
                        'notice_date' => $assetOrLocation->maintainable->next_maintenance_date,
                        'link' => match ($assetOrLocation->maintainable->maintainable_type) {
                            'App\Models\Tenants\Site' => route('tenant.sites.show', $assetOrLocation->reference_code),
                            'App\Models\Tenants\Building' => route('tenant.buildings.show', $assetOrLocation->reference_code),
                            'App\Models\Tenants\Floor' => route('tenant.floors.show', $assetOrLocation->reference_code),
                            'App\Models\Tenants\Room' => route('tenant.rooms.show', $assetOrLocation->reference_code),
                            'App\Models\Tenants\Asset' => route('tenant.assets.show', $assetOrLocation->reference_code),
                        }
                    ]
                ];

                $assetOrLocation->notifications()->create([
                    ...$notification,
                    'scheduled_at' => $assetOrLocation->maintainable->next_maintenance_date->subDays($delayDays),
                    'notification_type' => 'next_maintenance_date',
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                ]);
            }
        }
    }

    private function searchEntity($modelClass, $column, $delayDays)
    {
        return $modelClass::whereHas('maintainable', fn($query) => $query->where($column, '>', Carbon::now()->addDays($delayDays)))->get();
    }
}
