<?php

namespace Database\Factories\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Maintainable;
use App\Models\Tenants\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class RoomFactory extends Factory
{

    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locationType = LocationType::where('level', 'room')->first();
        $level = Floor::first();

        $count = Room::where('location_type_id', $locationType->id)->count();

        $codeNumber = generateCodeNumber($count, $locationType->prefix, 3);

        $code = $level->reference_code . '-' .  $codeNumber;

        return [
            'surface_floor' => fake()->numberBetween(100, 3000),
            'surface_walls' => fake()->numberBetween(100, 3000),
            'reference_code' => $code,
            'code' => $codeNumber,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Room $room) {

                $room->maintainable()->save(
                    Maintainable::factory()->make()
                );
            }
        );
    }
}
