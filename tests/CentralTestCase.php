<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class CentralTestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're in central context
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    protected function tearDown(): void
    {
        // Make sure we end any tenant context
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }
}
