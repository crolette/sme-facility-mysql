<?php

namespace Database\Factories\Tenants;

use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Maintainable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenants\User>
 */
class SiteFactory extends Factory
{

    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $location = LocationType::where('level', 'site')->first();

        return [
            'location_type_id' => $location->id,
            'surface_floor' => fake()->numberBetween(100, 3000),
            'surface_walls' => fake()->numberBetween(100, 3000)
        ];
    }

    public function configure()
    {
        return $this->afterCreating(

            function (Site $site) {

                $location = $site->locationType;

                $count = Site::where('location_type_id', $location->id)->where('id', '<', $site->id)->count();

                $codeNumber = generateCodeNumber($count + 1, $location->prefix);

                $site->update([
                    'reference_code' => $codeNumber,
                    'code' => $codeNumber,
                ]);

                $site->maintainable()->save(
                    Maintainable::factory()->make()
                );
            }
        );
    }
}
