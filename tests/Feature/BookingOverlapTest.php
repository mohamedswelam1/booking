<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\CarbonImmutable;

class BookingOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_book_occupied_slot(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $service = Service::factory()->create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'duration' => 60,
            'price' => 50.00,
        ]);

        $start = CarbonImmutable::now()->addDay()->setTime(10, 0);
        Booking::factory()->create([
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'service_id' => $service->id,
            'start_time' => $start,
            'end_time' => $start->addMinutes(60),
            'status' => 'confirmed',
            'total_price' => 50.00,
        ]);

        $this->actingAs($customer)
            ->postJson('/api/v1/bookings', [
                'service_id' => $service->id,
                'start_time' => $start->toIso8601String(),
            ])
            ->assertStatus(422);
    }
}


