<?php

namespace Database\Factories\Tenants;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Asset;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Document;
use App\Models\Tenants\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     **/
    protected $model = Document::class;

    protected array $customAttributes = [];

    public function withCustomAttributes(array $attributes): static
    {
        $this->customAttributes = $attributes;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakeName = $this->faker->words(3, true);
        $extension = 'pdf';

        $categoryType = CategoryType::where('category', 'document')->first();

        $user = $this->customAttributes['user'];
        $directoryName = $this->customAttributes['directoryName'];
        $model = $this->customAttributes['model'];

        $directory = tenancy()->tenant->id . '/' . $directoryName . '/' . $model->id . '/documents';

        $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_' . Str::slug($fakeName, '-') . '_' .  Str::substr(Str::uuid(), 0, 8) . '.' . $extension;

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
