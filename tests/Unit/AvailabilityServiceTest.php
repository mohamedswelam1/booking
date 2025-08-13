<?php

namespace Tests\Unit;

use App\Contracts\Repositories\AvailabilityRepositoryContract;
use App\Contracts\Repositories\BookingRepositoryContract;
use App\Models\Availability;
use App\Models\AvailabilityOverride;
use App\Models\Service;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Mockery as m;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    public function test_calculates_available_slots(): void
    {
        $provider = new User(['id' => '01HW', 'role' => 'provider']);
        $service = new Service(['duration' => 60]);

        $availabilityRepo = m::mock(AvailabilityRepositoryContract::class);
        $bookingRepo = m::mock(BookingRepositoryContract::class);

        $availabilityRepo->shouldReceive('getRecurringForProvider')->andReturn([
            new Availability(['day_of_week' => 3, 'start_time' => '10:00', 'end_time' => '12:00', 'timezone' => 'UTC']),
        ]);
        $availabilityRepo->shouldReceive('getOverrides')->andReturn([]);
        $bookingRepo->shouldReceive('existsConfirmedOverlap')->andReturnFalse();

        $serviceUnderTest = new AvailabilityService($availabilityRepo, $bookingRepo);

        $wednesday = CarbonImmutable::parse('2025-01-15T00:00:00Z'); // Wednesday
        $slots = $serviceUnderTest->getAvailableSlots($provider, $service, $wednesday);

        $this->assertCount(2, $slots);
        $this->assertSame('2025-01-15T10:00:00+00:00', $slots[0]->toIso8601String());
        $this->assertSame('2025-01-15T11:00:00+00:00', $slots[1]->toIso8601String());
    }
}


