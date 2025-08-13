<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_endpoint_has_rate_limiting(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::factory()->create();
        $service = Service::factory()->create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
        ]);

        $payload = [
            'service_id' => $service->id,
            'start_time' => CarbonImmutable::now()->addDay()->setTime(10, 0)->toIso8601String(),
        ];

        // Make 11 requests (rate limit is 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->actingAs($customer)
                ->postJson('/api/v1/bookings', $payload);
            
            if ($i < 10) {
                // First 10 should succeed or fail for business reasons (not rate limiting)
                $this->assertNotEquals(429, $response->getStatusCode());
            } else {
                // 11th should be rate limited
                $response->assertStatus(429);
            }
        }
    }
}
