<?php

namespace Database\Factories\Tenants;

use App\Models\Central\CategoryType;
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
        $category = CategoryType::where('category', 'provider')->first();

        return [
            'name' => $faker->company(),
            'email' => fake()->safeEmail(),
            'vat_number' => $faker->vat,
            'address' => $faker->address(),
            'phone_number' => $faker->phoneNumber(),
            'category_type_id' => $category->id,
            'website' => 'https://www.' . fake()->domainName()
        ];
    }
}
