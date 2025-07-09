<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TenantFactory extends Factory
{

    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('fr_BE');

        return [
            'id' => Str::lower(fake()->word()),
            'company_name' => $faker->company(),
            'email' => fake()->unique()->safeEmail(),
            'vat_number' => $faker->vat(false),
            'phone_number' => $faker->phoneNumber(),
            'company_code' => Str::random(4)
        ];
    }
}
