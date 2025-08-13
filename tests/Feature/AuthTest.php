<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_access_protected_route(): void
    {
        $this->getJson('/api/v1/bookings')->assertStatus(401);
    }
}


