<?php

namespace Database\Factories;

use App\Enums\LevelTypes;
use Illuminate\Support\Str;
use App\Models\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocationType>
 */
class LocationTypeFactory extends Factory
{
    protected $model = LocationType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = Str::lower(Str::random(10));

        $values = array_map(fn($case) => "{$case->value}", LevelTypes::cases());

        return [
            'slug' => $type,
            'prefix' => Str::upper(Str::substr($type, 0, 2)),
            'level' => $values[array_rand($values, 1)]
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (LocationType $locationType) {
            $translations = [
                'fr' => ucfirst($locationType->slug) . '_fr',
                'de' => ucfirst($locationType->slug) . '_de',
                'en' => ucfirst($locationType->slug) . '_en',
                'nl' => ucfirst($locationType->slug) . '_nl',
            ];

            foreach ($translations as $locale => $label) {
                $locationType->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }
        });
    }
}
