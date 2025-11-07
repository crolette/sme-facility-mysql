<?php

namespace App\Imports;

use Exception;
use App\Models\Translation;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use App\Services\ProviderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use App\Services\UserExportImportService;
use App\Models\Tenants\CountryTranslation;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Services\ProviderExportImportService;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class UsersDataImport implements ToCollection, WithHeadingRow, WithStartRow, WithValidation, WithCalculatedFormulas
{
    public function isEmptyWhen(array $row): bool
    {
        return $row['first_name'] === null && $row['last_name'] === null && $row['email'] === null;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            Log::info('GOGOGO', [$row]);
            try {
                $userHash = $row['hash'];

                $rowWithoutHash = $row;
                unset($rowWithoutHash['hash'], $rowWithoutHash['provider']);

                Log::info('rowWithoutHash', [$rowWithoutHash]);

                $calculatedHash = app(UserExportImportService::class)->calculateHash([...$rowWithoutHash]);
                Log::info('calculatedHash', [$index, $userHash, $calculatedHash]);
                if ($userHash !== $calculatedHash) {
                    Log::info('transformRowForProviderCreation before ');
                    $userData = $this->transformRowForProviderCreation($row);
                    Log::info('transformRowForProviderCreation after', [$userData]);

                    if ($row['id']) {
                        Log::info('CHANGE USER', [$row['id']]);
                        $provider = User::find($row['id']);
                        app(UserService::class)->update($provider, $userData);
                    } else {
                        $provider = app(UserService::class)->create($userData);
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
            'first_name' => $rowData['first_name'] ?? null,
            'last_name' => $rowData['last_name'] ?? null,
            'email' => $rowData['email'] ?? null,
            'phone_number' => $rowData['phone_number'] ?? null,
            'job_position' => $rowData['job_position'] ?? null,
            'provider_id' => $rowData['provider_id'] ?? null,
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

        if (isset($data['phone_number'])) {
            $data['phone_number'] = Str::startsWith($data['phone_number'], ['00']) ? str_replace('00', '+', $data["phone_number"]) : $data["phone_number"];
        }

        if (isset($data['provider'])) {

            $provider = Provider::where('name', $data['provider'])->first();

            if ($provider !== null) {
                $data = [...$data, 'provider_id' => $provider->id];
            }
        }

        return $data;
    }



    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:users,id',
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string',  'min:3', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail,],
            'job_position' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|regex:/^\+\d{8,15}$/|max:16',
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $index => $row) {
                if (isset($row['email'])) {
                    $exists = User::where('email', $row['email'])
                        ->when($row['id'] ?? null, fn($q) => $q->where('id', '!=', $row['id']))
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add($index . '.email', 'Cet email existe déjà.');
                    }
                }
            }
        });
    }
}
