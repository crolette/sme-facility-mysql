<?php

namespace Database\Factories\Tenants;

use App\Models\Tenants\Picture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PictureFactory extends Factory
{
    protected $model = Picture::class;


    protected array $customAttributes = [];

    public function withCustomAttributes(array $attributes): static
    {
        $this->customAttributes = $attributes;

        return $this;
    }

    public function definition(): array
    {
        $fakeName = $this->faker->words(3, true);
        $extension = 'jpg';

        $user = $this->customAttributes['user'];

        $directoryName = $this->customAttributes['directoryName'];
        $model = $this->customAttributes['model'];
        $directory = tenancy()->tenant->id . '/' . $directoryName . '/' . $model->id . '/pictures';

        $fileName = Carbon::now()->isoFormat('YYYYMMDD') . '_' . Str::slug($fakeName, '-') . '_' .  Str::substr(Str::uuid(), 0, 8) . '.' . $extension;

        return [
            'path' => $directory . '/' . $fileName,
            'filename' => $fileName,
            'directory' => $directory,
            'mime_type' => 'image/jpg',
            'size' => $this->faker->numberBetween(10000, 500000),
            'uploaded_by' => $user->id,
        ];
    }

    public function forType(Model $type): static
    {
        return $this->state([
            'documentable_id' => $type->id,
            'documentable_type' => get_class($type),
        ]);
    }
}
