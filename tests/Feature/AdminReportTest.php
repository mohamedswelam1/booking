<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_reports_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/bookings-summary')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total', 
                    'confirmed', 
                    'cancelled', 
                    'completed', 
                    'cancellation_rate'
                ]
            ]);
            
        // Test with provider filter
        $provider = User::factory()->create(['role' => 'provider']);
        $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/bookings-summary?provider_id=' . $provider->id)
            ->assertOk();
    }
}


