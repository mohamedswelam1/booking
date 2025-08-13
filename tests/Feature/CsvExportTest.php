<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_bookings_summary_as_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/bookings-summary/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('bookings-summary-', $response->headers->get('content-disposition'));
    }

    public function test_non_admin_cannot_export_csv(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->getJson('/api/v1/admin/reports/bookings-summary/export')
            ->assertStatus(403);
    }
}
