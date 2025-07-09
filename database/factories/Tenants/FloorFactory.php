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
        $location = LocationType::where('level', 'floor')->first();
        $building = Building::first();

        return [
            'location_type_id' => $location->id,
            'level_id' => $building->id
        ];
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Floor $floor) {

                $location = $floor->locationType;
                $building = $floor->building;

                $count = Floor::where('location_type_id', $location->id)->where('id', '<', $floor->id)->count();

                $code = generateCodeNumber($count, $location->prefix, 2);
                $referenceCode = $building->reference_code . '-' .  $code;

                $floor->update([
                    'reference_code' => $referenceCode,
                    'code' => $code,
                ]);

                $floor->maintainable()->save(
                    Maintainable::factory()->make()
                );
            }
        );
    }
}
