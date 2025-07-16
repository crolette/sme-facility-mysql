<?php

namespace Database\Factories\Tenants;

use Illuminate\Support\Str;
use App\Models\Tenants\User;
use App\Models\Tenants\Picture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PictureFactory extends Factory
{
    protected $model = Picture::class;



    public function definition()
    {
        return [
            'imageable_id' => '',
            'imageable_type' => '',
        ];
    }

    public function forModelAndUser($model, User $user, string $directoryName): static
    {
        return $this->state(function () use ($model, $user, $directoryName) {
            $fakeName = $this->faker->words(3, true);
            $extension = 'jpg';

            $directory = tenancy()->tenant->id . '/' . $directoryName . '/' . $model->id . '/pictures';

            $fileName = now()->isoFormat('YYYYMMDD') . '_' . Str::slug($fakeName, '-') . '_' . Str::substr(Str::uuid(), 0, 8) . '.' . $extension;

            return [
                'path' => $directory . '/' . $fileName,
                'filename' => $fileName,
                'directory' => $directory,
                'mime_type' => 'image/jpg',
                'size' => $this->faker->numberBetween(10000, 500000),
                'uploaded_by' => $user->id,
                'imageable_id' => $model->id,
                'imageable_type' => get_class($model),
            ];
        });
    }
}
