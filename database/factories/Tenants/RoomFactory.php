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

        if (!$locationType)
            $locationType = LocationType::factory()->create(['level' => 'room']);

        $level = Floor::first();

        return [
            'surface_floor' => fake()->numberBetween(100, 3000),
            'surface_walls' => fake()->numberBetween(100, 3000),
            'height' => fake()->numberBetween(2, 3),
            'location_type_id' => $locationType->id,
            'level_id' => $level->id
        ];
    }

    public function withMaintainableData(array $data = [])
    {
        return $this->afterCreating(function (Room $room) use ($data) {
            $maintainableData = array_merge([
                'name' => fake()->text(20),
                'description' => fake()->sentence(6),
            ], $data);

            $room->maintainable()->save(
                Maintainable::factory()->make($maintainableData)
            );
        });
    }


    public function configure()
    {
        return $this->afterCreating(

            function (Room $room) {

                $location = $room->locationType;
                $level = $room->floor;

                $count = Room::where('location_type_id', $location->id)->where('id', '<', $room->id)->count();

                $code = generateCodeNumber($count + 1, $location->prefix, 2);
                $referenceCode = $level->reference_code . '-' .  $code;

                $room->update([
                    'reference_code' => $referenceCode,
                    'code' => $code,
                ]);
            }
        );
    }
}
