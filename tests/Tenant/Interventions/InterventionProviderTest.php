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

use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenants\InterventionAction;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use App\Mail\SendInterventionToProviderEmail;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'asset']);

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});

// it('can factory intervention', function () {
//     Intervention::factory()->forLocation($this->asset)->create();
//     Intervention::factory()->forTicket($this->ticket)->create();
// });

it('can send an intervention to a provider', function() {

    Mail::fake();

        $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $formData = [
        'email' => 'test@test.com'
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) use ($intervention) {
        return $mail->hasTo('test@test.com');
    });

});

it('a provider can access to the intervention page', function () {

    Intervention::factory()->forLocation($this->asset)->create();
    $interventionOne = Intervention::factory()->forLocation($this->asset)->create();
    InterventionAction::factory()->forIntervention($interventionOne)->create();
    InterventionAction::factory()->forIntervention($interventionOne)->create();
    $interventionOne->update([
        'status' => InterventionStatus::COMPLETED->value
    ]);

    $interventionTwo = Intervention::factory()->forLocation($this->asset)->create();

    $actionTypesCount = CategoryType::where('category', 'action')->count();

    // route tested with signed middleware removed from routes
    $response = $this->getFromTenant('tenant.intervention.provider', $interventionTwo->id);
    $response->assertSessionHasNoErrors();
    $response->assertOk();
    $response->assertInertia(
        fn($page) => 
            $page->component('tenants/interventions/ProviderPage')
                ->has('intervention')
                ->has('actionTypes', $actionTypesCount)
                ->has('pastInterventions', 2)
                ->where('intervention.id', $interventionTwo->id)
    );


});


it('can post an action as external provider', function() {

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $provider = User::factory()->create();

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(2),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'creator_email' => $provider->email
    ];


    // route tested with signed middleware removed from routes
    $response = $this->postToTenant('tenant.intervention.provider.store', $formData, $intervention->id);
    $response->assertOk();

    assertDatabaseHas('intervention_actions', 
    [
        'intervention_id' => $intervention->id,
            'description' => 'New action for intervention',
            'intervention_date' => Carbon::now()->subDays(2)->toDateString(),
            'started_at' => '13:25',
            'finished_at' => '17:30',
            'intervention_costs' => '9999999.25',
            'creator_email' => $provider->email
    ]);

});

it('sends an email to the admin when a provider encoded a new action', function() {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $provider = User::factory()->create();

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(2),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'creator_email' => $provider->email
    ];


    // route tested with signed middleware removed from routes
    $response = $this->postToTenant('tenant.intervention.provider.store', $formData, $intervention->id);
    $response->assertOk();

    Mail::assertSent(InterventionAddedByProviderMail::class);
});