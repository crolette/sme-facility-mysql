<?php

namespace App\Services;

class NotificationTypesService
{
    private const NOTIFICATION_RULES = [
        'Asset' => [
            'depreciation_end_date' => 'Fin de dépréciation',
            'next_maintenance_date' => 'Maintenance programmée',
            'end_warranty_date' => 'Fin de garantie'
        ],
        'Location' => [
            'next_maintenance_date' => 'Maintenance programmée'
        ],
        'Contract' => [
            'notice_date' => 'Préavis de résiliation',
            'end_date' => 'Fin de contrat'
        ],
        'Interventions' => [
            'planned_at' => 'Intervention planifiée'
        ]
    ];

    public function getAvailableTypesFor(string $assetType): array
    {
        return array_keys(self::NOTIFICATION_RULES[$assetType] ?? []);
    }

    public function getLabelFor(string $assetType, string $notificationType): string
    {
        return self::NOTIFICATION_RULES[$assetType][$notificationType] ?? $notificationType;
    }

    public function isValidCombination(string $assetType, string $notificationType): bool
    {
        return isset(self::NOTIFICATION_RULES[$assetType][$notificationType]);
    }

    public function getAllAssetTypes(): array
    {
        return array_keys(self::NOTIFICATION_RULES);
    }
}
