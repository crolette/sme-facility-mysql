<?php

namespace Database\Factories\Tenants;

use App\Models\Tenants\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('fr_BE');

        return [
            'name' => $faker->company(),
            'vat_number' => $faker->vat(false),
            'address' => $faker->address()
            // 'phone_number' => $faker->phoneNumber(),
        ];
    }
}
