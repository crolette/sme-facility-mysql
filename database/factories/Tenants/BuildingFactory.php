<?php

namespace Database\Factories\Tenants;

use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Building;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = LocationType::where('level', 'building')->first();
        $siteLocation = Site::first();


        return [
            'surface_floor' => fake()->numberBetween(100, 3000),
            'surface_walls' => fake()->numberBetween(100, 3000),
            'location_type_id' => $location->id,
            'level_id' => $siteLocation->id

        ];
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Building $building) {

                $location = $building->locationType;
                $site = $building->site;

                $count = Building::where('location_type_id', $location->id)->where('level_id', $site->id)->where('id', '<', $building->id)->count();

                $code = generateCodeNumber($count + 1, $location->prefix);

                $referenceCode = $site->reference_code . '-' . $code;

                $building->update([
                    'reference_code' => $referenceCode,
                    'code' => $code,
                ]);

                $building->maintainable()->save(
                    Maintainable::factory()->make()
                );
            }
        );
    }
}
