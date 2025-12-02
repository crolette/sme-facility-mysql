<?php

namespace Database\Factories;

use App\Enums\AddressTypes;
use App\Models\Address;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{

    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('fr_BE');

        $countries = ['BE', 'GB', 'DE', 'FR', 'NL'];

        return [
            'street' => fake()->streetName(),
            'house_number' => fake()->buildingNumber(),
            'zip_code' => $faker->postcode(),
            'city' => $faker->cityName(),
            'country' => $countries[array_rand($countries, 1)],
            'address_type' => AddressTypes::COMPANY->value

        ];
    }
}
