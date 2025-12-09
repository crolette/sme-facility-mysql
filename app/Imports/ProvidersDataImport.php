<?php

namespace App\Imports;

use Exception;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use App\Services\ProviderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use App\Models\Tenants\CountryTranslation;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Services\ProviderExportImportService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ProvidersDataImport implements ToCollection, WithHeadingRow, WithStartRow, WithValidation, WithCalculatedFormulas
{
    public function isEmptyWhen(array $row): bool
    {
        return $row['name'] === null;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $providerHash = $row['hash'] ?? null;

                $rowWithoutHash = $row;
                unset($rowWithoutHash['hash']);

                Log::info('rowWithoutHash', ['row' => $rowWithoutHash]);

                $calculatedHash = app(ProviderExportImportService::class)->calculateHash([...$rowWithoutHash]);

                if ($providerHash !== $calculatedHash) {
                    $providerData = $this->transformRowForProviderCreation($row);

                    if ($row['id']) {
                        $provider = Provider::find($row['id']);
                        app(ProviderService::class)->update($provider, $providerData);
                    } else {
                        $provider = app(ProviderService::class)->create($providerData);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error row " . ($index + 2), [
                    'data' => $row->toArray(),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function transformRowForProviderCreation($rowData)
    {
        $data = [
            'id' => $rowData['id'] ?? null,
            'name' => $rowData['name'] ?? null,
            'email' => $rowData['email'] ?? null,
            'website' => $rowData['website'] ?? null,
            'vat_number' => $rowData['vat_number'] ?? null,
            'phone_number' => $rowData['phone_number'],
            'street' => $rowData['street'],
            'house_number' => $rowData['house_number'] ?? null,
            'postal_code' => $rowData['postal_code'] ?? null,
            'city' => $rowData['city'] ?? null,
            'country_code' => $rowData['country_code'] ?? null,
            'categories' => $rowData['categories'] ?? null,
        ];

        return $data;
    }


    public function startRow(): int
    {
        return 3;
    }

    public function prepareForValidation($data)
    {
        if (isset($data['email'])) {
            $data['email'] = Str::lower($data['email']);
        }

        if (isset($data['website'])) {
            $data['website'] = Str::startsWith($data['website'], ['http', 'https']) ? Str::lower($data['website']) : Str::lower('https://' . $data['website']);
        }

        if (isset($data['phone_number'])) {
            $data['phone_number'] = Str::startsWith($data['phone_number'], ['00']) ? str_replace('00', '+', $data["phone_number"]) : $data["phone_number"];
        }

        if (isset($data['house_number'])) {
            $data['house_number'] = strval($data['house_number']);
        }

        if (isset($data['postal_code'])) {
            $data['postal_code'] = strval($data['postal_code']);
        }

        $categories = [];

        for ($i = 1; $i < 4; $i++) {
            $categoryOne = Translation::where('translatable_type', CategoryType::class)
                ->where('label', $data['category_' . $i])
                ->whereHasMorph('translatable', [CategoryType::class], function (Builder $query) {
                    $query->where('category', 'provider');
                })
                ->first();

            if ($categoryOne !== null) {
                $categories = [...$categories, $categoryOne->translatable];
            }
        }

        $data['categories'] = $categories;

        Log::info('categories prepare validation', $data['categories']);

        $countryTranslation = CountryTranslation::where('label', $data['country'])->first();

        if ($countryTranslation !== null) {
            $data['country_code'] = $countryTranslation->country->iso_code;
        }

        return $data;
    }



    public function rules(): array
    {
        return [
            'id' => 'nullable|exists:providers,id',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail,],
            'name' => ['required', 'string', 'max:255'],

            'street' => 'required|string|max:100',
            'house_number' => 'nullable|string|max:10',
            'postal_code' => 'required|string|max:8',
            'city' => 'required|string|max:100',
            'country_code' => ['required', 'string', 'exists:countries,iso_code'],

            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/', 'max:14',],
            'phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'website' => 'nullable|url:http,https',
            'categories' => 'array|min:1',
            // 'categoryId' => ['required', Rule::in(CategoryType::where('category', 'provider')->pluck('id')->toArray())],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $index => $row) {
                if (isset($row['email'])) {
                    $exists = Provider::where('email', $row['email'])
                        ->when($row['id'] ?? null, fn($q) => $q->where('id', '!=', $row['id']))
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add($index . '.email', 'Cet email existe déjà.');
                    }
                }

                if (isset($row['vat_number'])) {
                    $exists = Provider::where('vat_number', $row['vat_number'])
                        ->when($row['id'] ?? null, fn($q) => $q->where('id', '!=', $row['id']))
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add($index . '.vat_number', 'Cet vat_number existe déjà.');
                    }
                }
            }
        });
    }
}
