<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Models\Category;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class BookingRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $provider;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter before each test
        RateLimiter::clear('bookings:' . request()->ip());

        // Create test users
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->provider = User::factory()->create(['role' => 'provider']);

        // Create test data
        $category = Category::factory()->create();
        $this->service = Service::factory()->create([
            'provider_id' => $this->provider->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_booking_creation_rate_limit_for_customers()
    {
        $this->actingAs($this->customer);

        // Make 5 booking requests (should be allowed)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/bookings', [
                'service_id' => $this->service->id,
                'start_time' => now()->addDays($i + 1)->format('Y-m-d H:i:s'),
            ]);

            if ($i < 5) {
                $this->assertNotEquals(429, $response->status(), "Request $i should not be rate limited");
            }
        }

        // 6th request should be rate limited
        $response = $this->postJson('/api/v1/bookings', [
            'service_id' => $this->service->id,
            'start_time' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Too many booking creation attempts. Please wait'
        ]);
        $this->assertArrayHasKey('retry_after_seconds', $response->json('data'));
    }

    public function test_booking_actions_rate_limit_for_providers()
    {
        $this->actingAs($this->provider);

        // Create some bookings to confirm
        $bookings = Booking::factory(25)->create([
            'provider_id' => $this->provider->id,
            'service_id' => $this->service->id,
            'status' => 'pending',
        ]);

        // Make 20 confirmation requests (should be allowed for providers)
        foreach ($bookings->take(20) as $index => $booking) {
            $response = $this->postJson("/api/v1/bookings/{$booking->id}/confirm");
            
            $this->assertNotEquals(429, $response->status(), "Confirmation request $index should not be rate limited");
        }

        // 21st request should be rate limited
        $booking = $bookings->get(20);
        $response = $this->postJson("/api/v1/bookings/{$booking->id}/confirm");

        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error'
        ]);
        $this->assertStringContainsString('booking confirmation', $response->json('message'));
    }

    public function test_booking_read_rate_limit()
    {
        $this->actingAs($this->customer);

        // Make 60 read requests (should be allowed)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/v1/bookings');
            $this->assertNotEquals(429, $response->status(), "Read request $i should not be rate limited");
        }

        // 61st request should be rate limited
        $response = $this->getJson('/api/v1/bookings');
        $response->assertStatus(429);
        $this->assertStringContainsString('booking retrieval', $response->json('message'));
    }

    public function test_availability_check_rate_limit()
    {
        // Test public availability endpoint (no auth required)
        
        // Make 30 availability requests (should be allowed)
        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson("/api/v1/services/{$this->service->id}/availability");
            $this->assertNotEquals(429, $response->status(), "Availability request $i should not be rate limited");
        }

        // 31st request should be rate limited
        $response = $this->getJson("/api/v1/services/{$this->service->id}/availability");
        $response->assertStatus(429);
        $this->assertStringContainsString('availability check', $response->json('message'));
    }

    public function test_different_users_have_separate_rate_limits()
    {
        // Customer 1 hits their limit
        $customer1 = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer1);

        // Exhaust customer1's rate limit
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/bookings', [
                'service_id' => $this->service->id,
                'start_time' => now()->addDays($i + 1)->format('Y-m-d H:i:s'),
            ]);
        }

        // Customer1's next request should be rate limited
        $response = $this->postJson('/api/v1/bookings', [
            'service_id' => $this->service->id,
            'start_time' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);
        $response->assertStatus(429);

        // Customer 2 should still be able to make requests
        $customer2 = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer2);

        $response = $this->postJson('/api/v1/bookings', [
            'service_id' => $this->service->id,
            'start_time' => now()->addDays(20)->format('Y-m-d H:i:s'),
        ]);

        $this->assertNotEquals(429, $response->status(), "Customer 2 should not be affected by customer 1's rate limit");
    }

    public function test_rate_limit_headers_are_present()
    {
        $this->actingAs($this->customer);

        $response = $this->postJson('/api/v1/bookings', [
            'service_id' => $this->service->id,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        // Check that rate limit headers are present
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    public function test_general_api_rate_limiting()
    {
        $this->actingAs($this->customer);

        // The general API rate limit should be much higher
        // This test ensures it doesn't interfere with normal usage
        
        $response = $this->getJson('/api/v1/services');
        $this->assertNotEquals(429, $response->status());
        
        // Check rate limit headers for general API
        $response->assertHeader('X-RateLimit-Limit');
        $this->assertTrue($response->headers->get('X-RateLimit-Limit') >= 60);
    }

    protected function tearDown(): void
    {
        // Clear all rate limiters after each test
        RateLimiter::clear('bookings');
        RateLimiter::clear('booking-actions');
        RateLimiter::clear('booking-read');
        RateLimiter::clear('availability');
        RateLimiter::clear('api');

        parent::tearDown();
    }
}
