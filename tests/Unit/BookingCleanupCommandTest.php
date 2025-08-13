<?php

namespace Tests\Unit;

use App\Console\Commands\BookingCleanup;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_command_cancels_old_pending_bookings(): void
    {
        // Create old pending booking (older than 24h)
        $oldBooking = Booking::factory()->create([
            'status' => 'pending',
            'created_at' => CarbonImmutable::now()->subHours(25),
        ]);

        // Create recent pending booking (within 24h)
        $recentBooking = Booking::factory()->create([
            'status' => 'pending',
            'created_at' => CarbonImmutable::now()->subHours(12),
        ]);

        // Create old confirmed booking (should not be affected)
        $oldConfirmedBooking = Booking::factory()->create([
            'status' => 'confirmed',
            'created_at' => CarbonImmutable::now()->subHours(25),
        ]);

        $this->artisan(BookingCleanup::class)
            ->expectsOutput('Cancelled 1 pending bookings older than 24 hours.')
            ->assertExitCode(0);

        // Refresh models
        $oldBooking->refresh();
        $recentBooking->refresh();
        $oldConfirmedBooking->refresh();

        $this->assertEquals('cancelled', $oldBooking->status);
        $this->assertEquals('pending', $recentBooking->status);
        $this->assertEquals('confirmed', $oldConfirmedBooking->status);
    }
}
