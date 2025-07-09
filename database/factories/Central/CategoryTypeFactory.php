<?php

namespace Database\Factories\Central;

use Illuminate\Support\Str;
use App\Enums\CategoryTypes;
use App\Models\Central\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CategoryTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $values = array_map(fn($case) => "{$case->value}", CategoryTypes::cases());

        return [
            'slug' => Str::slug(fake()->text(20)),
            'category' => $values[array_rand($values, 1)]
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (CategoryType $categoryType) {
            $translations = [
                'fr' => ucfirst($categoryType->slug) . '_fr',
                'de' => ucfirst($categoryType->slug) . '_de',
                'en' => ucfirst($categoryType->slug) . '_en',
                'nl' => ucfirst($categoryType->slug) . '_nl',
            ];

            foreach ($translations as $locale => $label) {
                $categoryType->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }
        });
    }
}
