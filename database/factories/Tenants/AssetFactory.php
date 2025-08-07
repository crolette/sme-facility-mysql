<?php

namespace Database\Factories\Tenants;

use App\Models\Tenants\Asset;
use App\Models\Tenants\Company;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use App\Models\Central\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AssetFactory extends Factory
{

    protected $model = Asset::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $category = CategoryType::where('category', 'asset')->first();
        // Remplissage temporaire avant de définir les 

        return [
            'surface' => fake()->numberBetween(1, 10),
            'category_type_id' => $category->id,
            'location_type' => null,
            'location_id' => null,
            'reference_code' => null,
            'code' => null,
        ];
    }

    public function forLocation($location): static
    {
        return $this->for($location, 'location')->state(function () use ($location) {
            $count = Company::incrementAndGetTicketNumber();

            $codeNumber = generateCodeNumber($count, 'A', 4);

            $referenceCode = $location->reference_code . '-' . $codeNumber;

            return [
                'reference_code' => $referenceCode,
                'code' => $codeNumber
            ];
        });
    }


    public function configure()
    {
        return $this->afterCreating(

            function (Asset $asset) {

                $asset->maintainable()->save(
                    Maintainable::factory()->make()
                );
            }
        );
    }
}
