<?php

use Carbon\Carbon;
use App\Imports\UsersImport;
use App\Models\Tenants\User;
use App\Enums\NoticePeriodEnum;
use App\Enums\ContractTypesEnum;
use App\Imports\ContractsImport;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Support\Facades\Storage;
use App\Services\UserExportImportService;
use function PHPUnit\Framework\assertNull;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertNotEmpty;

beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    User::factory()->withRole('Maintenance Manager')->create(['email' => 'crolweb@gmail.com']);
    $this->actingAs($this->admin, 'tenant');



    $this->providerOne = Provider::factory()->create([
        'name' => 'Company A',
    ]);

    $this->providerTwo = Provider::factory()->create([
        'name' => 'Company B',
    ]);
});

it('can import and create new contracts', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('contracts.xlsx', file_get_contents(base_path('tests/fixtures/contracts.xlsx')));

    Excel::import(new ContractsImport, $file);

    assertDatabaseHas(
        'contracts',
        [
            'name' => 'Contract One',
            'type' => ContractTypesEnum::MAINTENANCE,
            'contract_duration' => ContractDurationEnum::ONE_YEAR,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'provider_id' => null,
        ],
    );

    assertDatabaseHas(
        'contracts',
        [
            'name' => 'Contract Two',
            'internal_reference' => 'ALLIN',
            'provider_reference' => '123ALL',

            'type' => ContractTypesEnum::ALLIN,
            'contract_duration' => ContractDurationEnum::SIX_MONTHS,
            'notice_period' => NoticePeriodEnum::DEFAULT,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addMonths(6)->toDateString(),
            'notice_date' => Carbon::now()->addMonths(6)->subDays(7)->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::MANUAL,
            'status' => ContractStatusEnum::CANCELLED,
            'provider_id' => 1,
        ],
    );

    assertDatabaseHas(
        'contracts',
        [
            'name' => 'Contract Three',
            'type' => ContractTypesEnum::CLEANING,
            'contract_duration' => ContractDurationEnum::TWO_YEARS,
            'notice_period' => NoticePeriodEnum::ONE_MONTH,
            'start_date' => Carbon::createFromDate(2025, 7, 1)->toDateString(),
            'end_date' => Carbon::createFromDate(2025, 7, 1)->addYears(2)->toDateString(),
            'notice_date' => Carbon::createFromDate(2025, 7, 1)->addYears(2)->subMonth()->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'provider_id' => 2,
        ],
    );
});

it('can update existing contracts by import', function () {

    Contract::factory()->create([
        'name' => 'Contract to update',
    ]);

    Contract::factory()->create([
        'name' => 'Contract two',
    ]);


    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('contracts_update.xlsx', file_get_contents(base_path('tests/fixtures/contracts_update.xlsx')));

    Excel::import(new ContractsImport, $file);

    assertDatabaseHas(
        'contracts',
        [
            'name' => 'Updated first contract',
            'type' => ContractTypesEnum::MAINTENANCE,
            'internal_reference' => 'MAINT',
            'provider_reference' => 'ONEMAINT',
            'contract_duration' => ContractDurationEnum::ONE_YEAR,
            'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC,
            'status' => ContractStatusEnum::ACTIVE,
            'provider_id' => 1,
        ],
    );

    assertDatabaseHas(
        'contracts',
        [
            'name' => 'Contract Two',
            'internal_reference' => 'ALLIN',
            'provider_reference' => '123ALL',
            'type' => ContractTypesEnum::ALLIN,
            'contract_duration' => ContractDurationEnum::SIX_MONTHS,
            'notice_period' => NoticePeriodEnum::DEFAULT,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addMonths(6)->toDateString(),
            'notice_date' => Carbon::now()->addMonths(6)->subDays(7)->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::MANUAL,
            'status' => ContractStatusEnum::CANCELLED,
            'provider_id' => 2,
        ],
    );
});

// it('fails when the name of the file does not contain users', function () {

//     Storage::fake('local');

//     $file = UploadedFile::fake()->createWithContent('providers.xlsx', file_get_contents(base_path('tests/fixtures/providers.xlsx')));

//     $formData = ['file' => $file];

//     $response = $this->postToTenant('api.tenant.import.users', $formData, [], [
//         'Content-Type' => 'multipart/form-data'
//     ]);
//     $response->assertJson(['status' => 'error', 'message' => 'Wrong file. The file name should include users']);
// });

// it('does not update user with no changes', function () {
//     assertDatabaseHas(
//         'users',
//         [
//             'first_name' => 'Brad',
//             'last_name' => 'Pitt',
//             'email' => 'bradpitt@actorstudio.com',
//             'job_position' => 'Actor',
//             'phone_number' => '+32654821379'
//         ],
//     );

//     Storage::fake('local');

//     $file = UploadedFile::fake()->createWithContent('users.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

//     Excel::import(new UsersImport, $file);

//     assertDatabaseHas(
//         'users',
//         [
//             'first_name' => 'Brad',
//             'last_name' => 'Pitt',
//             'email' => 'bradpitt@actorstudio.com',
//             'job_position' => 'Actor',
//             'phone_number' => '+32654821379'
//         ],
//     );
// });

// it('can import and update users', function () {

//     assertDatabaseHas(
//         'users',
//         [
//             'id' => 3,
//             'first_name' => 'Michael',
//             'last_name' => 'Jones',
//             'provider_id' => null,
//         ],
//     );

//     assertDatabaseHas(
//         'users',
//         [
//             'id' => 4,
//             'first_name' => 'Josiane',
//             'last_name' => 'Balasko',
//             'phone_number' => null,
//             'job_position' => null,
//             'provider_id' => 2,
//         ],
//     );


//     Storage::fake('local');

//     $file = UploadedFile::fake()->createWithContent('users.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

//     Excel::import(new UsersImport, $file);

//     assertDatabaseHas(
//         'users',
//         [
//             'id' => 3,
//             'first_name' => 'Miguel',
//             'last_name' => 'Paquito',
//             'email' => 'miguelpaquito@gmail.com',
//             'job_position' => 'Incognito mode',
//             'phone_number' => '+32987654321',
//             'provider_id' => 1,
//         ],
//     );

//     assertDatabaseHas(
//         'users',
//         [
//             'id' => 4,
//             'first_name' => 'Josiane',
//             'last_name' => 'Balasko',
//             'email' => 'jobalasko@icloud.com',
//             'job_position' => 'Sales Manager',
//             'phone_number' => '+32852963147',
//             'provider_id' => null,
//         ],
//     );
// });
