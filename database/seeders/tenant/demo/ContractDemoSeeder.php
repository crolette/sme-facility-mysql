<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Asset;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Country;
use App\Services\QRCodeService;
use Illuminate\Database\Seeder;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;

class ContractDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Contract


        // Téléphonie Orange
        $provider = Provider::where('name', 'Orange sa')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Abonnements Orange Basic',
            'type' => ContractTypesEnum::OTHER->value,
            'internal_reference' => 'PHONE',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::yesterday()->subYears(2),
            'contract_duration' => ContractDurationEnum::TWO_YEARS->value,
            'end_date' => ContractDurationEnum::TWO_YEARS->addTo(Carbon::yesterday()->subYears(2)),
            'notice_period' => null,
            'notice_date' => null,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::EXPIRED,
            'notes' => fake()->text(50),
        ]);

        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Abonnements Orange Premium',
            'type' => ContractTypesEnum::OTHER->value,
            'internal_reference' => 'PHONE',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::now()->subMonths(6),
            'contract_duration' => ContractDurationEnum::TWO_YEARS->value,
            'end_date' => ContractDurationEnum::TWO_YEARS->addTo(Carbon::now()->subMonths(6)),
            'notice_period' => NoticePeriodEnum::ONE_MONTH->value,
            'notice_date' => NoticePeriodEnum::ONE_MONTH->subFrom(ContractDurationEnum::TWO_YEARS->addTo(Carbon::now()->subMonths(6))),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ]);


        $provider = Provider::where('name', 'Le comptoir de la ram')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Maintenance IT',
            'type' => ContractTypesEnum::ONDEMAND->value,
            'internal_reference' => 'PC Repair',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::yesterday()->subYears(2),
            'contract_duration' => ContractDurationEnum::SIX_MONTHS->value,
            'end_date' => ContractDurationEnum::SIX_MONTHS->addTo(Carbon::yesterday()->subYears(2)),
            'notice_period' => null,
            'notice_date' => null,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::CANCELLED,
            'notes' => fake()->text(50),
        ]);
    }
}
