<?php

namespace Database\Factories;

use App\Enums\AddressTypes;
use App\Models\Address;
use App\Models\Tenants\Country;
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





        return [
            'street' => fake()->streetName(),
            'house_number' => fake()->buildingNumber(),
            'zip_code' => $faker->postcode(),
            'city' => $faker->cityName(),

            'address_type' => AddressTypes::COMPANY->value

        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Address $address) {
            $countries = ['BE', 'GB', 'DE', 'FR', 'NL'];

            $country = Country::where('iso_code_a2', array_rand($countries, 1))->first();

            $address->country()->associate($country);
        });
    }
}
