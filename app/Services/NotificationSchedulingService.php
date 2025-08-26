<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Enums\ScheduledNotificationStatusEnum;


class NotificationSchedulingService
{
    public function scheduleForContract(Contract $contract)
    {
        Debugbar::info('NotificationSchedulingService - scheduleForContract');
        dump('NotificationSchedulingService - scheduleForContract', $contract->name);

        // Exemple de JSON
        // [
        //     'asset/location name' => 'Photocopieur Xerox'/'Rez-de-chaussée',
        //     'due_date' => '2024-12-31',
        //     'dashboard_url' => 'https://app.com/contracts/15'
        //      
        //     'contract_name' => 'Contrat nettoyage',
        //     'supplier_name' => 'Entreprise XYZ',
        //     'contract_reference' => 'CNT-2024-001',
        // ]

        $notification = [
            'recipient_name' => 'TEST',
            'recipient_email' => 'test@test.com',
            'notification_type' => 'contract',
            'scheduled_at' => Carbon::now()->subDays(15),
            'status' => ScheduledNotificationStatusEnum::PENDING->value,
            'data' => [
                'subject' => 'test',
                'notice_date' => $contract->notice_date
            ]
        ];

        $users = User::role('Admin')->get();

        foreach ($users as $user) {
            $contract->notifications()->create([
                ...$notification,
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
        }
    }
}
