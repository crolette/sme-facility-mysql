<?php

namespace Database\Factories\Tenants;

use Carbon\Carbon;
use App\Models\Tenants\Ticket;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\InterventionAction;
use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InterventionActionFactory extends Factory
{
    protected $model = InterventionAction::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actionType = CategoryType::where('category', 'action')->first();
        return [
            'action_type_id' => $actionType->id,
            'description' => fake()->paragraph(),
            'intervention_date' => Carbon::now(),
            'started_at' => '13:58',
            'finished_at' => '15:20',
            'intervention_costs' => 500.25,
            'creator_email' => fake()->safeEmail()
        ];
    }

    public function forIntervention($intervention)
    {
        return $this->state(function () use ($intervention) {

            return [
                'intervention_id' => $intervention->id,
            ];
        });
    }
}
