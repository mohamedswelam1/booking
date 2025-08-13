<?php

namespace App\Services;

use App\Contracts\AvailabilityContract;
use App\Contracts\Repositories\AvailabilityRepositoryContract;
use App\Contracts\Repositories\BookingRepositoryContract;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

class AvailabilityService implements AvailabilityContract
{
    public function __construct(
        private readonly AvailabilityRepositoryContract $availability,
        private readonly BookingRepositoryContract $bookings,
    ) {
    }

    public function getAvailableSlots(User $provider, Service $service, CarbonInterface $day): array
    {
        $day = CarbonImmutable::parse($day)->utc();
        $recurring = $this->availability->getRecurringForProvider($provider);
        $overrides = $this->availability->getOverrides($provider, $day, $day->addDays(1));

        $serviceDuration = $service->duration;
        $slots = [];

        foreach ($recurring as $availability) {
            if ((int) $availability->day_of_week !== $day->isoWeekday()) {
                continue;
            }

            $startLocal = Carbon::parse($availability->start_time, $availability->timezone)->setDate($day->year, $day->month, $day->day);
            $endLocal = Carbon::parse($availability->end_time, $availability->timezone)->setDate($day->year, $day->month, $day->day);

            $windowStartUtc = $startLocal->copy()->utc();
            $windowEndUtc = $endLocal->copy()->utc();

            for ($slot = $windowStartUtc->copy(); $slot->lt($windowEndUtc); $slot = $slot->addMinutes($serviceDuration)) {
                $slotEnd = $slot->copy()->addMinutes($serviceDuration);
                if ($slotEnd->gt($windowEndUtc)) {
                    break;
                }
                // Skip if overlaps confirmed booking
                if ($this->bookings->existsConfirmedOverlap($provider, $slot, $slotEnd)) {
                    continue;
                }
                // Apply overrides
                $isBlocked = false;
                foreach ($overrides as $override) {
                    $overrideStart = CarbonImmutable::parse($override->date.' '.$override->start_time, $availability->timezone)->utc();
                    $overrideEnd = CarbonImmutable::parse($override->date.' '.$override->end_time, $availability->timezone)->utc();
                    $overlaps = $slot->lt($overrideEnd) && $slotEnd->gt($overrideStart);
                    if ($overlaps) {
                        if ($override->is_available) {
                            // allowed: leave as available
                        } else {
                            $isBlocked = true;
                            break;
                        }
                    }
                }
                if ($isBlocked) {
                    continue;
                }
                $slots[] = $slot;
            }
        }

        return $slots;
    }

    /**
     * Returns an associative array of date => list of ISO8601 slot strings for next N days.
     */
    public function nextDaysSlots(User $provider, Service $service, int $days = 7): array
    {
        $day = CarbonImmutable::now('UTC');
        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $day->addDays($i);
            $slots = $this->getAvailableSlots($provider, $service, $d);
            $result[$d->toDateString()] = array_map(fn ($c) => $c->toIso8601String(), $slots);
        }
        return $result;
    }

    public function getRecurring(User $provider): array
    {
        return $this->availability->getRecurringForProvider($provider);
    }

    public function upsertRecurring(User $provider, array $entries): void
    {
        $this->availability->upsertRecurring($provider, $entries);
    }
}


