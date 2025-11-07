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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenants\InterventionAction;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use App\Mail\InterventionAddedByProviderMail;
use App\Mail\SendInterventionToProviderEmail;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset =  Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});


it('can send an intervention to a provider and to multiple emails/users and assign it to this provider', function () {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $formData = [
        'provider_id' => $this->provider->id,
        'emails' => ['test@test.com', 'testa@test.com']
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertJson(['status' => 'success']);

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });
    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) {
        return $mail->hasTo('testa@test.com');
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($this->provider),
            'assignable_id' => $this->provider->id,
        ]
    );
});

it('can send an intervention to only one internal user and assign it to him', function () {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();


    $formData = [
        'user_id' => $user->id,
        'emails' => ['test@test.com']
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) use ($intervention) {
        return $mail->hasTo('test@test.com');
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );
});

it('cannot send an intervention to several internal users', function () {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();


    $formData = [
        'user_id' => $user->id,
        'emails' => ['test@test.com', 'testa@test.com']
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertNotSent(SendInterventionToProviderEmail::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });

    assertDatabaseMissing(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );
});

it('sends email only once even if one email is multiple times', function () {
    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();


    $formData = [
        'provider_id' => $this->provider->id,
        'emails' => ['test@test.com', 'test@test.com']
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });

    Mail::assertSentCount(1);
});

it('cannot send an intervention to a non-existing provider', function () {
    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();


    $formData = [
        'provider_id' => 10,
        'email' => 'test@test.com'
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertJson(['errors' => ['provider_id' => ['The selected provider id is invalid.']]]);

    Mail::assertNotSent(SendInterventionToProviderEmail::class, function ($mail) use ($intervention) {
        return $mail->hasTo('test@test.com');
    });
});

it('cannot send an intervention to a non-existing user', function () {
    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $formData = [
        'user_id' => 10,
        'email' => 'test@test.com'
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertJson(['errors' => ['user_id' => ['The selected user id is invalid.']]]);

    Mail::assertNotSent(SendInterventionToProviderEmail::class, function ($mail) use ($intervention) {
        return $mail->hasTo('test@test.com');
    });
});

it('cannot send an intervention without user_id or provider_id', function () {
    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $formData = [
        'email' => 'test@test.com'
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertJson(['status' => 'error']);

    Mail::assertNotSent(SendInterventionToProviderEmail::class, function ($mail) use ($intervention) {
        return $mail->hasTo('test@test.com');
    });
});

it('can send an intervention to a new user, assign it to him and remove the old one', function () {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();


    $formData = [
        'user_id' => $user->id,
        'emails' => [$user->email]
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );

    $newUser = User::factory()->create();

    $formData = [
        'user_id' => $newUser->id,
        'emails' => [$newUser->email]
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) use ($newUser) {
        return $mail->hasTo($newUser->email);
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($newUser),
            'assignable_id' => $newUser->id,
        ]
    );

    assertDatabaseMissing(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );
});

it('can send an intervention to a user, assign it to him and remove the old provider', function () {

    Mail::fake();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $user = User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        'user_id' => $user->id,
        'emails' => [$user->email]
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );

    $newUser = User::factory()->create();

    $formData = [
        'provider_id' => $this->provider->id,
        'emails' => ['test@test.com']
    ];

    $response = $this->postToTenant('api.interventions.send-provider', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(SendInterventionToProviderEmail::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });

    assertDatabaseHas(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($this->provider),
            'assignable_id' => $this->provider->id,
        ]
    );

    assertDatabaseMissing(
        'interventions',
        [
            'id' => $intervention->id,
            'assignable_type' => get_class($user),
            'assignable_id' => $user->id,
        ]
    );
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

    $response = $this->getFromTenant('tenant.intervention.provider', $interventionTwo->id);
    $response->assertSessionHasNoErrors();
    $response->assertOk();
    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/interventions/InterventionProviderPage')
            ->has('intervention')
            ->has('actionTypes', $actionTypesCount)
            ->has('pastInterventions', 2)
            ->where('intervention.id', $interventionTwo->id)
    );
});

it('can post an action as external provider and intervention reassigned by default to admin', function () {

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


    // signed middleware has to be remove from the route to test
    $response = $this->postToTenant('tenant.intervention.provider.store', $formData, $intervention->id);
    $response->assertOk();

    assertDatabaseHas(
        'intervention_actions',
        [
            'intervention_id' => $intervention->id,
            'description' => 'New action for intervention',
            'intervention_date' => Carbon::now()->subDays(2)->toDateString(),
            'started_at' => '13:25',
            'finished_at' => '17:30',
            'intervention_costs' => '9999999.25',
            'creator_email' => $provider->email
        ]
    );

    assertDatabaseHas('interventions', [
        'id' => $intervention->id,
        'assignable_type' => get_class($this->admin),
        'assignable_id' => $this->admin->id,
    ]);
});

it('sends an email to the admin when a provider encoded a new action and intervention reassigned to admin', function () {
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

    Mail::assertSent(InterventionAddedByProviderMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });

    assertDatabaseHas('interventions', [
        'id' => $intervention->id,
        'assignable_type' => get_class($this->admin),
        'assignable_id' => $this->admin->id,
    ]);
});

it('sends an email to the maintenance manager when a provider encoded a new action and intervention reassigned to manager', function () {
    Mail::fake();

    $user = User::factory()->withRole('Maintenance Manager')->create();
    $this->asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

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

    Mail::assertSent(InterventionAddedByProviderMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    assertDatabaseHas('interventions', [
        'id' => $intervention->id,
        'assignable_type' => get_class($user),
        'assignable_id' => $user->id,
    ]);
});

it('can upload pictures for an intervention action', function () {

    $user = User::factory()->withRole('Maintenance Manager')->create();
    $this->asset->maintainable()->update(['maintenance_manager_id' => $user->id]);
    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $provider = User::factory()->create();

    $file1 = UploadedFile::fake()->image('action1.jpg');
    $file2 = UploadedFile::fake()->image('action1.png');

    $formData = [
        'action_type_id' => $this->interventionActionType->id,
        'description' => 'New action for intervention',
        'intervention_date' => Carbon::now()->subDays(7),
        'started_at' => '13:25',
        'finished_at' => '17:30',
        'intervention_costs' => '9999999.25',
        'creator_email' => $provider->email,
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.interventions.actions.store', $formData, $intervention);

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
