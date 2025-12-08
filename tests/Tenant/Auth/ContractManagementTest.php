<?php

use Carbon\Carbon;

use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertNotNull;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'document']);
    CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->create(['category' => 'provider']);
    Site::factory()->withMaintainableData()->create();
    Building::factory()->create();
    Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    $this->provider = Provider::factory()->create();
    $this->contract = Contract::factory()->create();

    $this->formData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];
});

test('test access roles to contracts index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.contracts.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to create contract page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.contracts.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to view any contract page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.contracts.show', $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store a contract', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.contracts.store', $this->formData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update any contract page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.contracts.edit', $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update a contract', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->patchToTenant('api.contracts.update', $this->formData, $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);


test('test access roles to contract page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.contracts.show', $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);


test('test access roles to delete any contract', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.contracts.destroy', $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to post a new document to a contract', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $file1 = UploadedFile::fake()->image('avatar.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = ['files' => [
        [
            'file' => $file1,
            'name' => 'FILE 1 - Long name of more than 10 chars',
            'description' => 'descriptionIMG',
            'typeId' => $categoryType->id,
            'typeSlug' => $categoryType->slug
        ],
    ]];

    $response = $this->postToTenant('api.contracts.documents.post', $formData, $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to detach a document from a contract', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'contracts',
        'model' => $this->contract,
    ])->create();
    $this->contract->documents()->attach($document);

    $formData = [
        'document_id' => $document->id
    ];

    $response = $this->patchToTenant('api.contracts.documents.detach', $formData, $this->contract);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);
