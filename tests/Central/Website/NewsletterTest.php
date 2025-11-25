<?php

use App\Mail\ContactMail;
use App\Enums\CategoryTypes;
use App\Enums\ContactReasons;
use App\Mail\ContactDemoMail;
use App\Models\Central\CentralUser;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware();
});

it('can post a form request for newsletter', function () {

    $formData = [
        'email' => 'janedoe@janedoe.com',
        'consent' => true
    ];


    $response = $this->post(route('website.newsletter', $formData));
    $response->assertSessionHasNoErrors();
    $response->assertJson(['status' => 'success']);

    assertDatabaseHas(
        'newsletter',
        [
            'email' => 'janedoe@janedoe.com',
            'consent' => 1
        ]
    );
});

it('cannot post a form request for newsletter without consent', function () {

    $formData = [
        'email' => 'janedoe@janedoe.com',
        'consent' => false
    ];


    $response = $this->post(route('website.newsletter', $formData));
    $response->assertSessionHasErrors(
        ['consent' => "The consent field must be accepted."]
    );

    assertDatabaseMissing(
        'newsletter',
        [
            'email' => 'janedoe@janedoe.com',
            'consent' => 1
        ]
    );
});

it('cannot post two same emails', function () {
    $formData = [
        'email' => 'janedoe@janedoe.com',
        'consent' => true
    ];


    $response = $this->post(route('website.newsletter', $formData));

    $formData = [
        'email' => 'janedoe@janedoe.com',
        'consent' => true
    ];


    $response = $this->post(route('website.newsletter', $formData));
    $response->assertJson(['status' => 'error']);
});
