<?php

namespace App\Imports;

use Error;
use Exception;
use Throwable;
use Carbon\Carbon;
use Maatwebsite\Excel\Row;
use App\Models\Translation;
use Illuminate\Support\Str;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use App\Services\ProviderService;
use Illuminate\Support\Collection;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use App\Services\MaintainableService;
use Barryvdh\Debugbar\Facades\Debugbar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\OnEachRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Tenants\CountryTranslation;
use App\Services\AssetExportImportService;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Services\ProviderExportImportService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithEvents;

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
                $providerHash = $row['hash'];

                $rowWithoutHash = $row;
                unset($rowWithoutHash['hash']);

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
            'categoryId' => $rowData['categoryId'] ?? null,
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

        $translation = Translation::where('translatable_type', CategoryType::class)
            ->where('label', $data['category'])
            ->whereHasMorph('translatable', [CategoryType::class], function (Builder $query) {
                $query->where('category', 'provider');
            })
            ->first();

        if ($translation !== null) {
            $data['categoryId'] = $translation->translatable_id;
        }

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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail],
            'name' => ['required', 'string', 'max:255'],

            'street' => 'required|string|max:100',
            'house_number' => 'nullable|string|max:10',
            'postal_code' => 'required|string|max:8',
            'city' => 'required|string|max:100',
            'country_code' => ['required', 'string', 'exists:countries,iso_code'],

            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/', 'max:14'],
            'phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'website' => 'nullable|url:http,https',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:' . Provider::maxUploadSizeKB(),
            'categoryId' => ['required', Rule::in(CategoryType::where('category', 'provider')->pluck('id')->toArray())],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // dump($validator);
            $data = $validator->getData();

            if (isset($data['id'])) {
                $exists = Provider::where('email', $data['email'])->where('id', '<>', $data['id'])->exists();

                if ($exists) {
                    $validator->errors()->add('email', 'DOUBLE EMAIL');
                }

                $exists = Provider::where('vat_number', $data['vat_number'])->where('id', '<>', $data['id'])->exists();

                if ($exists) {
                    $validator->errors()->add('vat_number', 'DOUBLE vat_number');
                }
            }

            if (isset($data['vat_number'])) {
                $exists = Provider::where('vat_number', $data['vat_number'])->exists();

                if ($exists) {
                    $validator->errors()->add('vat_number', 'DOUBLE vat_number');
                }
            }
        });
    }
}
