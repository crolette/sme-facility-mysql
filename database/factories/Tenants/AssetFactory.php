<?php

namespace Database\Factories\Tenants;

use App\Models\Tenants\Asset;
use App\Models\Tenants\Company;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Carbon\Carbon;
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
        if (!$category)
            $category = CategoryType::factory()->create(['category' => 'asset']);


        $randomDepreciationDuration = fake()->randomDigitNotZero();
        return [
            'surface' => fake()->numberBetween(1, 10),
            'category_type_id' => $category->id,
            'brand' => fake()->company,
            'model' => fake()->word(),
            'serial_number' => fake()->randomLetter() . fake()->randomNumber(4, true),
            'depreciable' => true,
            "depreciation_start_date" => Carbon::now(),
            "depreciation_end_date" => Carbon::now()->addYear($randomDepreciationDuration),
            "depreciation_duration" =>  $randomDepreciationDuration,
            'location_type' => null,
            'location_id' => null,
            'reference_code' => null,
            'code' => null,
        ];
    }

    public function withMaintainableData(array $data = [])
    {
        return $this->afterCreating(function (Asset $asset) use ($data) {
            $maintainableData = array_merge([
                'name' => fake()->text(20),
                'description' => fake()->sentence(6),
            ], $data);

            $asset->maintainable()->save(
                Maintainable::factory()->make($maintainableData)
            );
        });
    }

    public function forLocation($location): static
    {
        return $this->for($location, 'location')->state(function () use ($location) {
            $count = Company::incrementAndGetAssetNumber();

            $codeNumber = generateCodeNumber($count, 'A', 4);

            $referenceCode = $location->reference_code . '-' . $codeNumber;

            return [
                'reference_code' => $referenceCode,
                'code' => $codeNumber
            ];
        });
    }
}
