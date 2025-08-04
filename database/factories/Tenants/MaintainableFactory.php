<?php

namespace Database\Factories\Tenants;

use App\Models\Tenants\User;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenants\Maintainable>
 */
class MaintainableFactory extends Factory
{

    protected $model = Maintainable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => fake()->text(20),
            'description' => fake()->sentence(6),
            'maintenance_manager_id' => User::first()->id
        ];
    }
}
