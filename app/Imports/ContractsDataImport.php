<?php

namespace App\Imports;

use Exception;
use Carbon\Carbon;
use App\Models\Translation;
use Illuminate\Support\Str;
use App\Enums\NoticePeriodEnum;
use Illuminate\Validation\Rule;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Rules\NotDisposableEmail;
use App\Services\ContractService;
use App\Services\ProviderService;
use Illuminate\Support\Collection;
use App\Enums\ContractDurationEnum;
use Illuminate\Support\Facades\Log;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Tenants\CountryTranslation;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Services\ContractExportImportService;
use App\Services\ProviderExportImportService;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ContractsDataImport implements ToCollection, WithHeadingRow, WithStartRow, WithValidation, WithCalculatedFormulas
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

                $calculatedHash = app(ContractExportImportService::class)->calculateHash([...$rowWithoutHash]);

                if ($providerHash !== $calculatedHash) {
                    $contractData = $this->transformRowForContractCreation($row);

                    if ($row['id']) {
                        $contract = Contract::find($row['id']);
                        app(ContractService::class)->update($contract, $contractData);
                    } else {
                        $contract = app(ContractService::class)->create($contractData);
                    }

                    if (isset($contractData['provider']))
                        app(ContractService::class)->associateProviderToContractWhenImport($contract, $contractData['provider']);
                }
            } catch (\Exception $e) {
                Log::error("Error row " . ($index + 2), [
                    'data' => $row->toArray(),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function transformRowForContractCreation($rowData)
    {
        $data = [
            'id' => $rowData['id'] ?? null,
            'name' => $rowData['name'] ?? null,
            'type' => $rowData['type'] ?? null,
            'internal_reference' => $rowData['internal_reference'] ?? null,
            'provider_reference' => $rowData['provider_reference'] ?? null,
            'contract_duration' => $rowData['contract_duration'],
            'notice_period' => $rowData['notice_period'],
            'start_date' => $rowData['start_date'] ?? null,
            'end_date' => $rowData['end_date'] ?? null,
            'notice_date' => $rowData['notice_date'] ?? null,
            'renewal_type' => $rowData['renewal_type'] ?? null,
            'status' => $rowData['status'] ?? null,
            'notes' => $rowData['notes'] ?? null,
            'provider' => $rowData['provider'] ?? null,
        ];

        return $data;
    }


    public function startRow(): int
    {
        return 3;
    }

    public function prepareForValidation($data)
    {
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::instance(Date::excelToDateTimeObject($data['start_date']));
            $endDate = ContractDurationEnum::from($data['contract_duration'])->addTo($data['start_date']);
        } else {
            $data['start_date'] = Carbon::now();
            $endDate = ContractDurationEnum::from($data['contract_duration'])->addTo(Carbon::now());
        }

        $data['end_date'] = $endDate;

        if (isset($data['notice_period'])) {
            $data['notice_date']  = NoticePeriodEnum::from($data['notice_period'])->subFrom($data['end_date']);
        }

        if (!isset($data['type'])) {
            $data['type']  = ContractTypesEnum::OTHER->value;
        }

        return $data;
    }



    public function rules(): array
    {

        return [
            'id' => 'nullable|exists:contracts,id',
            'name' => 'required|string|min:4|max:100',
            'type' => ['nullable', Rule::in(array_column(ContractTypesEnum::cases(), 'value'))],
            'notes' => 'nullable|string|min:4|max:250',

            'internal_reference' => 'nullable|string|max:50',
            'provider_reference' => 'nullable|string|max:50',

            'start_date' => 'nullable|date',
            'contract_duration' => ['nullable', Rule::in(array_column(ContractDurationEnum::cases(), 'value'))],
            'end_date' => 'nullable|date',

            'notice_period' => ['nullable', Rule::in(array_column(NoticePeriodEnum::cases(), 'value'))],
            'notice_date' => ['nullable', 'date', 'after:start_date'],

            'renewal_type' => ['required', Rule::in(array_column(ContractRenewalTypesEnum::cases(), 'value'))],
            'status' => ['required', Rule::in(array_column(ContractStatusEnum::cases(), 'value'))],

        ];
    }
}
