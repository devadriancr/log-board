<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** The accounts are created by IT from the users screen. */
    public function test_public_registration_is_closed(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
