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
use App\Models\Tenants\Building;

use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
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

});

// it('can render the company profile page', function() {

//     $company = Company::factory()->create();

//     $response = $this->getFromTenant('tenant.company.show');

//     $response->assertInertia(
//         fn($page) =>
//         $page->component('settings/company')->has('company')->where('company.name', $company->name)->where('company.vat_number', $company->vat_number)
            
//     );
// });

it('can upload a new logo for the company', function() {
    $file1 = UploadedFile::fake()->image('logo.png');

    $formData = [
        'image' => $file1
    ];

    $response = $this->postToTenant('api.company.logo.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    Storage::disk('tenants')->assertExists(Company::first()->logo);
});
