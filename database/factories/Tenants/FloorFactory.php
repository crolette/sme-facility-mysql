<?php

namespace Database\Factories\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class FloorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locationType = LocationType::where('level', 'floor')->first();

        if (!$locationType)
            $locationType = LocationType::factory()->create(['level' => 'floor']);

        $building = Building::first();

        return [
            'surface_floor' => fake()->numberBetween(100, 3000),
            'surface_walls' => fake()->numberBetween(100, 3000),
            'location_type_id' => $locationType->id,
            'level_id' => $building->id
        ];
    }

    public function withMaintainableData(array $data = [])
    {
        return $this->afterCreating(function (Floor $floor) use ($data) {
            $maintainableData = array_merge([
                'name' => fake()->text(20),
                'description' => fake()->sentence(6),
            ], $data);

            $floor->maintainable()->save(
                Maintainable::factory()->make($maintainableData)
            );
        });
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Floor $floor) {

                $location = $floor->locationType;
                $building = $floor->building;

                $count = Floor::where('location_type_id', $location->id)->where('id', '<', $floor->id)->count();

                $code = generateCodeNumber($count + 1, $location->prefix, 2);
                $referenceCode = $building->reference_code . '-' .  $code;

                $floor->update([
                    'reference_code' => $referenceCode,
                    'code' => $code,
                ]);
            }
        );
    }
}
