<?php

namespace Database\Factories\Tenants;

use App\Enums\TicketStatus;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TicketFactory extends Factory
{

    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $count = Ticket::all()->count();
        $codeNumber = generateCodeNumber($count, 'TK', 4);

        return [
            'code' => $codeNumber,
            'status' => TicketStatus::OPEN->value,
            'description' => fake()->paragraph(),
            'reported_by' => User::factory()->create()->id,
            'being_notified' => fake()->boolean(50),
        ];
    }

    public function anonymous()
    {
        return $this->state(fn() => [
            'reporter_id' => null,
            'reporter_email' => fake()->safeEmail,
        ]);
    }

    public function forLocation($location)
    {
        return $this->state(fn() => [
            'ticketable_type' => get_class($location),
            'ticketable_id' => $location->id,
        ]);
    }

    public function ongoing(): static
    {
        return $this->state(fn() => [
            'status' => TicketStatus::ONGOING->value,
        ]);
    }

    public function closed()
    {

        return $this->state(fn() => [
            'status' => TicketStatus::CLOSE->value,
            'closed_at' => now(),
            'closed_by' => User::factory(),
        ]);
    }
}
