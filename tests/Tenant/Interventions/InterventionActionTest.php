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

use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Queue;

use Illuminate\Support\Facades\Storage;
use App\Models\Tenants\InterventionAction;
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
    $this->room = Room::factory()->withMaintainableData()->create();
    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
    $this->intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create();
});

it('can create a new action to an intervention', function () {

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(7),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'created_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.interventions.actions.store', $formData, $this->intervention);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('intervention_actions', 2);
    assertDatabaseHas('intervention_actions', [
        'id' => 2,
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(7)->toDateString(),
        'started_at' => '13:25:00',
        'finished_at' => '17:30:00',
        'intervention_costs' => '9999999.25',
        'created_by' => $this->user->id
    ]);
});

it('can update an intervention action', function () {
    $interventionAction = InterventionAction::factory()->forIntervention($this->intervention)->create();

    assertDatabaseCount('intervention_actions', 2);

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'Updated action for intervention',
        'intervention_date' => Carbon::now()->subDays(2),
        'started_at' => '14:25',
        'finished_at' => '18:30',
        'intervention_costs' => '0',
        'updated_by' => $this->user->id,
    ];

    $response = $this->patchToTenant('api.interventions.actions.update', $formData, $interventionAction);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);


    $this->assertNotNull($interventionAction->creator_email);
    $this->assertNull($interventionAction->created_by);

    assertDatabaseHas('intervention_actions', [
        'id' => 2,
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'Updated action for intervention',
        'intervention_date' => Carbon::now()->subDays(2)->toDateString(),
        'started_at' => '14:25:00',
        'finished_at' => '18:30:00',
        'intervention_costs' => '0',
        'updated_by' => $this->user->id,
    ]);
});

it('can delete an intervention action', function () {
    $interventionAction = InterventionAction::factory()->forIntervention($this->intervention)->create();

    assertDatabaseCount('intervention_actions', 2);

    $response = $this->deleteFromTenant('api.interventions.actions.destroy', $interventionAction);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('intervention_actions', 1);
    assertDatabaseMissing('intervention_actions', [
        'id' => $interventionAction->id
    ]);
});

it('sums intervention costs of intervention when action with intervention_costs is added', function () {

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(7),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'created_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.interventions.actions.store', $formData, $this->intervention);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    // Intervention factory has 500.25
    assertDatabaseHas('interventions', [
        'id' => $this->intervention->id,
        'total_costs' => '10000499.50',
    ]);
});

it('updates intervention costs of intervention when action with intervention_costs is updated', function () {

    $interventionAction = $this->intervention->actions->first();

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'Updated action for intervention',
        'intervention_date' => Carbon::now()->subDays(2),
        'started_at' => '14:25',
        'finished_at' => '18:30',
        'intervention_costs' => '0',
        'updated_by' => $this->user->id,
    ];

    $response = $this->patchToTenant('api.interventions.actions.update', $formData, $interventionAction);
    $response->assertOk();

    assertDatabaseHas('interventions', [
        'id' => $this->intervention->id,
        'total_costs' => '0',
    ]);
});

it('updates intervention costs of intervention when action with intervention_costs is deleted', function () {
    InterventionAction::factory()->forIntervention($this->intervention)->create();
    $interventionAction = $this->intervention->actions->first();

    assertDatabaseHas('interventions', [
        'id' => $this->intervention->id,
        'total_costs' => '1000.50',
    ]);

    $response = $this->deleteFromTenant('api.interventions.actions.destroy', $interventionAction);
    $response->assertOk();

    assertDatabaseHas('interventions', [
        'id' => $this->intervention->id,
        'total_costs' => '500.25',
    ]);
});

it('can upload pictures for an intervention action', function () {
    Queue::fake();
    $file1 = UploadedFile::fake()->image('action1.jpg');
    $file2 = UploadedFile::fake()->image('action1.png');

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(7),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'created_by' => $this->user->id,
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.interventions.actions.store', $formData, $this->intervention);


    $interventionAction = InterventionAction::where('description', 'New action for intervention')->first();

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => get_class($interventionAction),
        'imageable_id' => $interventionAction->id
    ]);

    $pictures = $interventionAction->pictures;

    foreach ($pictures as $picture)
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
});
