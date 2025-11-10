<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Company;
use App\Enums\ContractTypesEnum;

use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;

use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;

use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Queue;

use App\Enums\ContractRenewalTypesEnum;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'provider']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset = Asset::factory()->forLocation(Room::first())->create();
    $this->contractOneData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];

    $this->contractTwoData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => ContractTypesEnum::MAINTENANCE->value,
        'notes' => 'Nouveau contrat de Sécurité 2025',
        'internal_reference' => 'Sécurité Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];
});

it('can create a contract with documents', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Contract',
        'documentable_id' => 1
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Contract',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::find(1)->path);
    Storage::disk('tenants')->assertExists(Document::find(2)->path);
});

it('can create a contract with documents and increase disk size accordingly', function () {

    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $this->postToTenant('api.contracts.store', $formData);
    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 3000);
});

it('can attach a document to an existing contract', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $categoryType = CategoryType::where('category', 'document')->first();
    $contract = Contract::factory()->create();

    $formData = ['files' => [
        [
            'file' => $file1,
            'name' => 'FILE 1 - Long name of more than 10 chars',
            'description' => 'descriptionIMG',
            'typeId' => $categoryType->id,
            'typeSlug' => $categoryType->slug
        ],
    ]];

    $response = $this->postToTenant('api.contracts.documents.post', $formData, $contract->id);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    $contract->refresh();
    assertEquals(1, $contract->documents->count());
    assertDatabaseCount('documents', 1);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Contract',
        'documentable_id' => 1
    ]);
});

it('can detach a document from an existing contract', function () {

    $contract = Contract::factory()->create();
    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'contracts',
        'model' => $contract,
    ])->create();
    $contract->documents()->attach($document);

    $formData = [
        'document_id' => $document->id
    ];

    $response = $this->patchToTenant('api.contracts.documents.detach', $formData, $contract->id);
    $response->assertOk();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $contract->id,
        'documentable_type' => Contract::class
    ]);
});

it('can attach an existing document to an existing contract', function () {

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
    ])->create();
    $contract = Contract::factory()->create();

    $formData = [
        'existing_documents' => [$document->id]
    ];

    $response = $this->postToTenant('api.contracts.documents.post', $formData, $contract->id);
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $contract->id,
        'documentable_type' => Contract::class
    ]);
});

it('deletes the documents & directory if a contract is deleted and documents are not linked to another asset/location', function () {

    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();
    $contract = Contract::find(1);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);

    $firstDocumentPath = Document::first()->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 0);

    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);
    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);

    Storage::disk('tenants')->assertMissing($contract->directory);
    Storage::disk('tenants')->assertMissing($firstDocumentPath);
    Storage::disk('tenants')->assertMissing($secondDocumentPath);
});

it('deletes the documents if a contract is deleted and documents are not linked to another asset/location and decrease disk size accordingly', function () {

    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $this->postToTenant('api.contracts.store', $formData);
    $contract = Contract::find(1);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 3000);

    $this->deleteFromTenant('api.contracts.destroy', $contract->id);

    $company->refresh();
    assertEquals(round($company->disk_size / 1024), 0);
});

it('deletes only document if the document is not linked to another asset/location', function () {

    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();
    $contract = Contract::find(1);

    $document = Document::first();

    $categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'floor',
        'categoryId' => $categoryType->id,
        'existing_documents' => [$document->id]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::find(2);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    $firstDocumentPath = Document::first()->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 1);

    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);
    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($contract),
        'documentable_id' => $contract->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    Storage::disk('tenants')->assertExists($firstDocumentPath);
    Storage::disk('tenants')->assertMissing($secondDocumentPath);
});

it('deletes only document if the document is not linked to another asset/location and decrease disk size accordingly', function () {
    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        ...$this->contractOneData,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();
    $contract = Contract::find(1);

    $document = Document::first();

    $categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'floor',
        'categoryId' => $categoryType->id,
        'existing_documents' => [$document->id]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 3000);



    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertSessionHasNoErrors();

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1000);
});
