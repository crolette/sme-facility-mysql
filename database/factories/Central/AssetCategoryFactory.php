<?php

namespace Database\Factories\Central;

use App\Models\Central\AssetCategory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AssetCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => Str::slug(fake()->text(20))
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (AssetCategory $assetCategory) {
            $translations = [
                'fr' => ucfirst($assetCategory->slug) . '_fr',
                'de' => ucfirst($assetCategory->slug) . '_de',
                'en' => ucfirst($assetCategory->slug) . '_en',
                'nl' => ucfirst($assetCategory->slug) . '_nl',
            ];

            foreach ($translations as $locale => $label) {
                $assetCategory->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }
        });
    }
}
