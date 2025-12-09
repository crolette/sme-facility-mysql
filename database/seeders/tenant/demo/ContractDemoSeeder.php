<?php

namespace Database\Seeders\tenant\demo;

use Carbon\Carbon;
use App\Models\Tenants\Room;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use Illuminate\Database\Seeder;
use App\Enums\ContractTypesEnum;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Enums\ContractRenewalTypesEnum;

class ContractDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Contract
        $roomOfficeDirector = Room::getByName('Bureau directeur')->first();

        $provider = Provider::where('name', 'All Clean sa')->first();
        $contract = Contract::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Nettoyage, entretien des locaux',
            'type' => ContractTypesEnum::CLEANING->value,
            'internal_reference' => 'CLEAN_2025-12',
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::tomorrow()->subYear(),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'end_date' => Carbon::tomorrow(),
            // 'notice_period' => NoticePeriodEnum::ONE_MONTH->value,
            // 'notice_date' => NoticePeriodEnum::ONE_MONTH->subFrom(Carbon::createFromDate(2025, 01, 12)->addYear()),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ]);

        $roomOfficeDirector->contracts()->attach($contract);
    }
}
