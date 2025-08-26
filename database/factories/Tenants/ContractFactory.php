<?php

namespace Database\Factories\Tenants;

use Carbon\Carbon;
use App\Models\Tenants\Asset;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Company;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use App\Models\Central\AssetCategory;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ContractFactory extends Factory
{

    protected $model = Contract::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::first()->id,
            'name' => fake()->word(),
            'type' => fake()->word(),
            'internal_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'provider_reference' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'start_date' => Carbon::now(),
            'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
            'end_date' => Carbon::now()->addMonth()->toDateString(),
            'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
            'notice_date' => Carbon::now()->addMonth()->subDays(14)->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'notes' => fake()->text(50),
        ];
    }

    public function forLocation($location)
    {
        return $this->afterCreating(function (Contract $contract) use ($location) {
            $location->contracts()->attach($contract);
        });
    }
}
