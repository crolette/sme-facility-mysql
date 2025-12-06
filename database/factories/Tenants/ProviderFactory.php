<?php

namespace Database\Factories\Tenants;

use Closure;
use App\Models\Tenants\Country;
use App\Models\Tenants\Provider;
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

        $country = Country::where('iso_code', 'BEL')->first();

        return [
            'name' => $faker->company(),
            'email' => fake()->safeEmail(),
            'vat_number' => $faker->vat,

            'street' => $faker->streetName(),
            'house_number' => $faker->buildingNumber(),
            'postal_code' => $faker->postCode(),
            'city' => $faker->cityName(),
            'country_id' => $country->id,

            'phone_number' => $faker->phoneNumber(),

            'website' => 'https://www.' . fake()->domainName()
        ];
    }

    public function configure()

    {
        $category = CategoryType::factory()->create(['category' => 'provider']);

        return $this->afterCreating(

            function (Provider $provider) use ($category) {
                $provider->categories()->attach($category->id);
            }
        );
    }
}
