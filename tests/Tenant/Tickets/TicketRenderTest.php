<?php

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;

use App\Models\Tenants\Ticket;
use App\Services\TenantLimits;
use App\Models\Tenants\Building;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    CategoryType::factory()->create(['category' => 'action']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $tenant = tenant();

    if ($tenant) {
        Cache::remember(
            "tenant:{$tenant->id}:limits",
            now()->addDay(),
            fn() => TenantLimits::loadLimitsFromDatabase($tenant)
        );
    }
});

it('can render the index tickets page', function () {

    Ticket::factory()->forLocation($this->asset)->create();
    Ticket::factory()->forLocation($this->asset)->ongoing()->create();
    Ticket::factory()->forLocation($this->asset)->closed()->create();

    $response = $this->getFromTenant('tenant.tickets.index');
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/IndexTickets')
    );
});


it('can render the show ticket page', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/ShowTicket')
            ->has('item')->where('item.code', $ticket->code)
    );
    $response->assertOk();
});

it('can render interventions in the ticket page', function () {
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    Intervention::factory()->withAction()->forTicket($ticket)->count(2)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/ShowTicket')
            ->has('item')
            ->has('item.interventions', 2)
            ->has('item.interventions.0.actions', 1)
    );
});
