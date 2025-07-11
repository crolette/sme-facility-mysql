<?php

namespace Database\Factories\Tenants;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Asset;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakeName = $this->faker->words(3, true);
        $extension = 'pdf';

        $categoryType = CategoryType::where('category', 'document')->first();
        $asset = Asset::first();
        $user = User::first();

        $directory = tenancy()->tenant->id . '/assets/' . $asset->id . '/documents';

        $fileName = Carbon::now()->isoFormat('YYYYMMDD') . '_' . Str::slug($fakeName, '-') . '_' .  Str::substr(Str::uuid(), 0, 8) . '.' . $extension;

        return [
            'path' => $directory . '/' . $fileName,
            'filename' => $fileName,
            'directory' => $directory,
            'name' => $fakeName,
            'description' => $this->faker->optional()->sentence(),
            'size' => $this->faker->numberBetween(10000, 500000), // 10Ko à 500Ko
            'mime_type' => 'application/pdf',
            'category_type_id' => $categoryType->id,
            'uploaded_by' => $user->id,
        ];
    }

    public function forAsset(Asset $asset): static
    {
        return $this->state([
            'documentable_id' => $asset->id,
            'documentable_type' => get_class($asset),
        ]);
    }
}
