<?php

namespace Database\Factories\Tenants;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProviderFactory extends Factory
{
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
            'email' => fake()->safeEmail(),
            'vat_number' => $faker->vat,
            'address' => $faker->address(),
            'phone_number' => $faker->phoneNumber(),
        ];
    }
}
