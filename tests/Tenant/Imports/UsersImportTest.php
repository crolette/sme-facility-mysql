<?php

use Carbon\Carbon;
use App\Imports\UsersImport;
use App\Models\Tenants\User;
use App\Models\Tenants\Provider;
use App\Jobs\ImportExcelUsersJob;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Queue;
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

    CategoryType::factory()->create([
        'category' => 'provider',
        'slug' => 'provider-horeca'
    ]);

    CategoryType::factory()->create([
        'category' => 'provider',
        'slug' => 'provider-security'
    ]);

    $this->providerOne = Provider::factory()->create([
        'name' => 'Company A',
    ]);

    $this->providerTwo = Provider::factory()->create([
        'name' => 'Company B',
    ]);

    $this->userA = User::factory()->create(['first_name' => 'Michael', 'last_name' => 'Jones']);
    $this->userB = User::factory()->create(['first_name' => 'Josiane', 'last_name' => 'Balasko', 'job_position' => null, 'phone_number' => null]);
    $this->userC = User::factory()->create(['first_name' => 'Brad', 'last_name' => 'Pitt', 'email' => 'bradpitt@actorstudio.com', 'job_position' => 'Actor', 'phone_number' => '+32654821379']);

    $this->userB->provider()->associate($this->providerTwo)->save();
});

it('can upload users and dispatch import users job', function () {

    Storage::fake('local');
    Queue::fake();

    $file = UploadedFile::fake()->createWithContent('contacts.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    $formData = ['file' => $file];

    $response = $this->postToTenant('api.tenant.import', $formData, [], [
        'Content-Type' => 'multipart/form-data'
    ]);
    Queue::assertPushed(ImportExcelUsersJob::class, function ($job) {
        return $job->user->id === Auth::id();
    });
});


it('can import and create new users', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('contacts.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    Excel::import(new UsersImport, $file);

    assertDatabaseHas(
        'users',
        [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'janedoe@janedoe.com',
            'job_position' => 'Incognito mode',
            'phone_number' => '+32987654321',
            'provider_id' => 1,
        ],
    );

    assertDatabaseHas(
        'users',
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@johndoe.com',
            'job_position' => 'Invisible man',
            'phone_number' => '+32123456789',
            'provider_id' => 2,
        ],
    );

    assertDatabaseHas(
        'users',
        [
            'first_name' => 'Jean',
            'last_name' => 'Valjean',
            'email' => 'jeanvaljean@jeanvaljean.com',
            'job_position' => null,
            'phone_number' => null,
            'provider_id' => null,
        ],
    );
});


it('fails when the name of the file does not contain contacts', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('users.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    $formData = ['file' => $file];

    $response = $this->postToTenant('api.tenant.import', $formData, [], [
        'Content-Type' => 'multipart/form-data'
    ]);
    $response->assertJson(['status' => 'error', 'message' => 'Wrong file.']);
});

it('does not update user with no changes', function () {
    assertDatabaseHas(
        'users',
        [
            'first_name' => 'Brad',
            'last_name' => 'Pitt',
            'email' => 'bradpitt@actorstudio.com',
            'job_position' => 'Actor',
            'phone_number' => '+32654821379'
        ],
    );

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('contacts.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    Excel::import(new UsersImport, $file);

    assertDatabaseHas(
        'users',
        [
            'first_name' => 'Brad',
            'last_name' => 'Pitt',
            'email' => 'bradpitt@actorstudio.com',
            'job_position' => 'Actor',
            'phone_number' => '+32654821379'
        ],
    );
});

it('can import and update users', function () {

    assertDatabaseHas(
        'users',
        [
            'id' => 3,
            'first_name' => 'Michael',
            'last_name' => 'Jones',
            'provider_id' => null,
        ],
    );

    assertDatabaseHas(
        'users',
        [
            'id' => 4,
            'first_name' => 'Josiane',
            'last_name' => 'Balasko',
            'phone_number' => null,
            'job_position' => null,
            'provider_id' => 2,
        ],
    );


    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('contacts.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    Excel::import(new UsersImport, $file);

    assertDatabaseHas(
        'users',
        [
            'id' => 3,
            'first_name' => 'Miguel',
            'last_name' => 'Paquito',
            'email' => 'miguelpaquito@gmail.com',
            'job_position' => 'Incognito mode',
            'phone_number' => '+32987654321',
            'provider_id' => 1,
        ],
    );

    assertDatabaseHas(
        'users',
        [
            'id' => 4,
            'first_name' => 'Josiane',
            'last_name' => 'Balasko',
            'email' => 'jobalasko@icloud.com',
            'job_position' => 'Sales Manager',
            'phone_number' => '+32852963147',
            'provider_id' => null,
        ],
    );
});
