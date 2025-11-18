<?php

use App\Enums\CategoryTypes;
use App\Enums\ContactReasons;
use App\Mail\ContactMail;
use App\Models\Central\CentralUser;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware();
});

it('renders the contact page', function () {

    $response = $this->get(route('website.contact'));
    $response->assertInertia(
        fn($page) =>
        $page->component('website/contact')
    );
});


it('can post a contact request', function () {

    Mail::fake();

    $formData = [
        'email' => 'test@test.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Company SA',
        'vat_number' => 'BE0123456789',
        'phone_number' => '+32123456789',
        'website' => 'www.sme-facility.com',
        'message' => fake()->words(25, true),
        'subject' => ContactReasons::APPOINTMENT->value,
    ];


    $response = $this->post(route('website.contact.post', $formData));
    $response->assertJson(['status' => 'success']);
    Mail::assertSent(ContactMail::class, function ($mail) {
        return $mail->hasTo('crolweb@gmail.com');
    });
    Mail::assertSent(ContactMail::class, function ($mail) {
        return $mail->hasReplyTo('test@test.com');
    });
});
