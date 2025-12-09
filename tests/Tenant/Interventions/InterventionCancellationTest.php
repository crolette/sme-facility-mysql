<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Building;

use App\Models\Tenants\Provider;
use App\Enums\InterventionStatus;
use Illuminate\Http\UploadedFile;

use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});


it('sets \'completed_at\' when intervention is updated with status cancelled', function () {
    Carbon::setTestNow(Carbon::now());
    $intervention = Intervention::factory()->withAction()->forLocation($this->room)->create(['planned_at' => Carbon::yesterday(), 'status' => InterventionStatus::PLANNED->value]);

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => InterventionStatus::CANCELLED->value,
        'description' => $intervention->description,
        'locationId' => $this->room->reference_code,
        'locationType' => get_class($this->room)
    ];

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $intervention->refresh();

    assertDatabaseHas('interventions', [
        'id' => $intervention->id,
        'priority' => 'medium',
        'status' => InterventionStatus::CANCELLED->value,
        'planned_at' => Carbon::yesterday()->toDateString(),
        'completed_at' => null,
        'cancelled_at' => Carbon::now()->toDateTimeString(),
    ]);
});

it('sets \'completed_at\' when intervention status changes to cancelled', function () {
    Carbon::setTestNow(Carbon::now());
    $intervention = Intervention::factory()->withAction()->forLocation($this->room)->create(['planned_at' => Carbon::yesterday(), 'status' => InterventionStatus::PLANNED->value]);

    $formData = [
        'status' => InterventionStatus::CANCELLED->value,
    ];

    $response = $this->patchToTenant('api.interventions.status', $formData, $intervention);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseHas('interventions', [
        'id' => $intervention->id,
        'priority' => 'medium',
        'status' => InterventionStatus::CANCELLED->value,
        'planned_at' => Carbon::yesterday()->toDateString(),
        'completed_at' => null,
        'cancelled_at' => Carbon::now()->toDateTimeString(),
    ]);
});
