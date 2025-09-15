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
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
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
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    CategoryType::factory()->create(['category' => 'provider']);
    $this->provider = Provider::factory()->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();

    $this->contractOneData = [
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

    $this->contractTwoData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => 'Sécurité',
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

it('can store an asset with contracts', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $asset = Asset::find(2);
    assertEquals(2, $asset->contracts()->count());
});

it('can store an asset with contracts and documents', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $file3 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $file4 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,

        'contracts' => [
            [...$this->contractOneData, 'files' => [
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
            ]],
            [...$this->contractTwoData, 'files' => [
                [
                    'file' => $file3,
                    'name' => 'FILE 3 - Long name of more than 10 chars',
                    'description' => 'descriptionIMG',
                    'typeId' => $categoryType->id,
                    'typeSlug' => $categoryType->slug
                ],
                [
                    'file' => $file4,
                    'name' => 'FILE 4 - Long name of more than 10 chars',
                    'description' => 'descriptionPDF',
                    'typeId' => $categoryType->id,
                    'typeSlug' => $categoryType->slug
                ]
            ]]
        ]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $asset = Asset::find(2);
    assertEquals(2, $asset->contracts()->count());

    assertDatabaseCount('documents', 4);
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
    assertDatabaseHas('documentables', [
        'document_id' => 3,
        'documentable_type' => 'App\Models\Tenants\Contract',
        'documentable_id' => 2
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 4,
        'documentable_type' => 'App\Models\Tenants\Contract',
        'documentable_id' => 2
    ]);
});



it('can add an existing contract when creating an asset', function() {

    $contract =  Contract::factory()->create();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,

        'existing_contracts' => [
            $contract->id,
            
            ]
        ];

        $response = $this->postToTenant('api.assets.store', $formData);
        $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

        $asset = Asset::find(2);

        assertDatabaseHas('contractables',
        [
            'contract_id' => $contract->id,
            'contractable_type' => get_class($asset),
            'contractable_id' => $asset->id
        ]);
        
    
});

it('can add multiple existing contracts when creating an asset', function () {

    $contractOne =  Contract::factory()->create();
    $contractTwo =  Contract::factory()->create();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,

        'existing_contracts' => [
            $contractOne->id,
            $contractTwo->id

        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $asset = Asset::find(2);

    assertDatabaseHas(
        'contractables',
        [
            'contract_id' => $contractOne->id,
            'contractable_type' => get_class($asset),
            'contractable_id' => $asset->id
        ]
    );
    assertDatabaseHas(
        'contractables',
        [
            'contract_id' => $contractTwo->id,
            'contractable_type' => get_class($asset),
            'contractable_id' => $asset->id
        ]
    );
});


it('can add existing contracts to an existing asset', function () {

    $contractOne =  Contract::factory()->create();
    $contractTwo =  Contract::factory()->create();

    $formData = [
        'existing_contracts' => [
            $contractOne->id,
            $contractTwo->id
        ]
    ];

    $response = $this->postToTenant('api.assets.contracts.post', $formData, $this->asset);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseCount('contractables', 2);
    assertDatabaseHas(
        'contractables',
        [
            'contract_id' => $contractOne->id,
            'contractable_type' => get_class($this->asset),
            'contractable_id' => $this->asset->id
        ]
    );

    assertDatabaseHas(
        'contractables',
        [
            'contract_id' => $contractTwo->id,
            'contractable_type' => get_class($this->asset),
            'contractable_id' => $this->asset->id
        ]
    );

});

it('can remove a contract from an asset', function() {

    $contractOne =  Contract::factory()->create();
    $contractTwo =  Contract::factory()->create();

    $formData = [
        'existing_contracts' => [
            $contractOne->id,
            $contractTwo->id
        ]
    ];

    $response = $this->postToTenant('api.assets.contracts.post', $formData, $this->asset);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseCount('contractables', 2);

    $formData = [
        'contract_id' => $contractOne->id
    ];

    $response = $this->deleteFromTenant('api.assets.contracts.delete',  $this->asset, $formData);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseCount('contractables', 1);

    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractOne->id,
            'contractable_type' => get_class($this->asset),
            'contractable_id' => $this->asset->id
        ]
    );

    assertDatabaseHas(
        'contractables',
        [
            'contract_id' => $contractTwo->id,
            'contractable_type' => get_class($this->asset),
            'contractable_id' => $this->asset->id
        ]
    );

});