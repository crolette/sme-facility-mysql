<?php

namespace Database\Factories\Tenants;

use Carbon\Carbon;
use App\Models\Tenants\Ticket;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\InterventionAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InterventionFactory extends Factory
{
    protected $model = Intervention::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = CategoryType::where('category', 'intervention')->first();
        $ticket = Ticket::first();

        return [
            'intervention_type_id' => $category->id,
            'priority' => 'medium',
            'status' => 'planned',
            'planned_at' => Carbon::now()->add('day', 7),
            'description' => fake()->paragraph(),
            'repair_delay' => Carbon::now()->add('month', 1),
            'ticket_id' => $ticket->id,
        ];
    }



    public function forLocation($location)
    {
        return $this->for($location, 'interventionable')->state(function () use ($location) {

            return [
                'maintainable_id' => $location->maintainable->id,
                'interventionable_type' => get_class($location),
                'interventionable_id' => $location->id,
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Intervention $intervention) {

                $intervention->actions()->save(
                    InterventionAction::factory()->make()
                );
            }
        );
    }
}
